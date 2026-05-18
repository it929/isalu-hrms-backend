<?php
namespace App\Http\Controllers\funds;
use App\Http\Controllers\Controller;
use Session;
use Illuminate\Http\Request;
use App\Http\Requests;
use App\Http\Controllers;
use Illuminate\Support\Str;
use Auth;
use DB;
use Fpdf;

class ReportController extends Controller
{
    public function __construct(Request $request)
    {
        $this->middleware(function ($request, $next) {
        $this->court    = $request->session()->get('current_court');

        //get search criteria sessions
           $this->sess_section    = $request->session()->get('search_section');
           $this->sess_division   = $request->session()->get('search_division');
           $this->sess_grade      = $request->session()->get('search_grade');
           $this->sess_fileNo     = $request->session()->get('search_fileNo');
           
       
                return $next($request);
        });
    }  
   
    public function index()
    {
        $data['courts'] = DB::table('tbl_court')->get();
        $data['division'] = DB::table('tbldivision')->where('courtID','=',$this->court)->get();
        $data['department'] = DB::table('tbldepartment')->where('courtID','=',$this->court)->get();
        $data['staff'] = DB::table('tblper')
        ->join('tbl_court','tbl_court.id','=','tblper.courtID')
        ->join('tbldesignation','tbldesignation.id','=','tblper.Designation')
        //->join('tbldesignation','tbldesignaion.departmentID','=','tblper.department')
      
        ->join('tbldepartment','tbldepartment.id','=','tblper.section')
        ->join('tbldivision','tbldivision.divisionID','=','tblper.divisionID')
        ->paginate(50);
        return view('staffReports/list',$data);
    }
 
    public function SearchStaff(Request $request)
    {
        $data['courts'] = DB::table('tbl_court')->get();
        $data['division'] = DB::table('tbldivision')->where('courtID','=',$this->court)->get();
        $data['department'] = DB::table('tbldepartment')->where('courtID','=',$this->court)->get();
        $court    = $request['Court'];
        $division = $request['division'];
        $section  = $request['section'];
        $fileNo   = $request['fileNo'];
        $grade    = $request['grade'];


        //create sessions for searches

        Session::put('search_court', $court);
        Session::put('search_division', $division);
        Session::put('search_section', $section);
        Session::put('search_grade', $grade);
        Session::put('search_fileNo', $fileNo);

        //dd($section);

        if($fileNo != '' && $division == '' && $grade == '' && $section == '')
        {
            $data['staff'] = DB::table('tblper')
        ->join('tbl_court','tbl_court.id','=','tblper.courtID')
        ->leftJoin('tbldesignation','tbldesignation.id','=','tblper.Designation')
        ->leftJoin('tbldepartment','tbldepartment.id','=','tblper.section')
        ->join('tbldivision','tbldivision.divisionID','=','tblper.divisionID')
        //->join('tbldesignation','tbldesignation.departmentID','=','tblper.department')
        //->join('tbldesignation','tbldesignation.grade','=','tblper.grade')
        ->where('tblper.fileNo','=',$fileNo)
        ->paginate(50);
        return view('staffReports/list',$data);
        }
        elseif ($court != '' && $division != '' && $grade != '' && $section != '')
        {
        $data['staff'] = DB::table('tblper')
        ->join('tbl_court','tbl_court.id','=','tblper.courtID')
        ->leftJoin('tbldesignation','tbldesignation.id','=','tblper.Designation')
        ->leftJoin('tbldepartment','tbldepartment.id','=','tblper.section')
        ->join('tbldivision','tbldivision.divisionID','=','tblper.divisionID')
//->join('tbldesignation','tbldesignation.departmentID','=','tblper.department')
        //->join('tbldesignation','tbldesignation.grade','=','tblper.grade')
        ->where('tblper.courtID','=',$court)
        ->where('tblper.section','=',$section)
        ->where('tblper.divisionID','=',$division)
        ->where('tblper.grade','=',$grade)
        ->orderby('tblper.section')
        ->orderby('tblper.divisionID')
        ->orderby('tblper.grade')
        ->paginate(50);
        return view('staffReports/list',$data);
        
        }
        elseif ($court != '' && $division != '' && $section == '')
        {
         $data['staff'] = DB::table('tblper')
        ->join('tbl_court','tbl_court.id','=','tblper.courtID')
        ->leftJoin('tbldesignation','tbldesignation.id','=','tblper.Designation')
        ->leftJoin('tbldepartment','tbldepartment.id','=','tblper.section')
        ->join('tbldivision','tbldivision.divisionID','=','tblper.divisionID')
        //->join('tbldesignation','tbldesignation.departmentID','=','tblper.department')
        //->join('tbldesignation','tbldesignation.grade','=','tblper.grade')
        ->where('tblper.courtID','=', $court)
        ->where('tblper.divisionID','=',$division)
        ->orderby('tblper.section')
        ->orderby('tblper.divisionID')
        ->orderby('tblper.grade')
        ->paginate(50);
        return view('staffReports/list',$data);
        }
        elseif ($court != '' && $division != '' && $section != '')
        {
        $data['staff'] = DB::table('tblper')
        ->join('tbl_court','tbl_court.id','=','tblper.courtID')
        ->leftJoin('tbldesignation','tbldesignation.id','=','tblper.Designation')
        ->leftJoin('tbldepartment','tbldepartment.id','=','tblper.section')
        ->join('tbldivision','tbldivision.divisionID','=','tblper.divisionID')
//->join('tbldesignation','tbldesignation.departmentID','=','tblper.department')
        //->join('tbldesignation','tbldesignation.grade','=','tblper.grade')
        ->where('tblper.courtID','=',$court)
        ->where('tblper.section','=',$section)
        ->where('tblper.divisionID','=',$division)
        ->orderby('tblper.section')
        ->orderby('tblper.divisionID')
        ->orderby('tblper.grade')
        ->paginate(50);
        
        return view('staffReports/list',$data);
        }
        elseif ($court != '' && $division == '' && $grade == '' && $section == '')
        {
        $data['staff'] = DB::table('tblper')
        ->join('tbl_court','tbl_court.id','=','tblper.courtID')
        ->leftJoin('tbldesignation','tbldesignation.id','=','tblper.Designation')
        ->leftJoin('tbldepartment','tbldepartment.id','=','tblper.section')
        ->join('tbldivision','tbldivision.divisionID','=','tblper.divisionID')
//->join('tbldesignation','tbldesignation.departmentID','=','tblper.department')
        //->join('tbldesignation','tbldesignation.grade','=','tblper.grade')
        ->where('tblper.courtID','=',$court)
        
        ->paginate(50);
        return view('staffReports/list',$data);
        
        }
        elseif ($court != '' && $division != '' && $grade != '' && $section == '' && $fileNo == '')
        {
        $data['staff'] = DB::table('tblper')
        ->join('tbl_court','tbl_court.id','=','tblper.courtID')
        ->leftJoin('tbldesignation','tbldesignation.id','=','tblper.Designation')
        ->leftJoin('tbldepartment','tbldepartment.id','=','tblper.section')
        ->join('tbldivision','tbldivision.divisionID','=','tblper.divisionID')
//->join('tbldesignation','tbldesignation.departmentID','=','tblper.department')
        //->join('tbldesignation','tbldesignation.grade','=','tblper.grade')
        ->where('tblper.courtID','=',$court)
        ->where('tblper.grade','=',$grade)
        ->where('tblper.division','=',$division)
        ->paginate(50);
        return view('staffReports/list',$data);
        
        }
        elseif ($court != '' && $division == '' && $grade != '' && $section == '' && $fileNo == '')
        {
        $data['staff'] = DB::table('tblper')
        ->join('tbl_court','tbl_court.id','=','tblper.courtID')
        ->leftJoin('tbldesignation','tbldesignation.id','=','tblper.Designation')
        ->leftJoin('tbldepartment','tbldepartment.id','=','tblper.section')
        ->join('tbldivision','tbldivision.divisionID','=','tblper.divisionID')
//->join('tbldesignation','tbldesignation.departmentID','=','tblper.department')
       // ->join('tbldesignation','tbldesignation.grade','=','tblper.grade')
        ->where('tblper.courtID','=',$court)
        ->where('tblper.grade','=',$grade)
        ->paginate(50);
        return view('staffReports/list',$data);
        
        }
        else
        {
        $data['staff'] = DB::table('tblper')
        ->join('tbl_court','tbl_court.id','=','tblper.courtID')
        ->leftJoin('tbldesignation','tbldesignation.id','=','tblper.Designation')
        ->leftJoin('tbldepartment','tbldepartment.id','=','tblper.section')
        ->join('tbldivision','tbldivision.divisionID','=','tblper.divisionID')
//->join('tbldesignation','tbldesignation.departmentID','=','tblper.department')
        //->join('tbldesignation','tbldesignation.grade','=','tblper.grade')
        ->orderby('tblper.section')
        ->orderby('tblper.divisionID')
        ->orderby('tblper.grade')
        ->paginate(50);
        return view('staffReports/list',$data);
        }
    }

    public function sessionset(Request $request)
    {
        
         $courtID = $request['courtID'];

         if($this->court == $courtID)
         {
           Session::forget('current_court');
         }

         $ses    = Session::put('current_court', $courtID);
         if($ses)
         {
            return response()->json("Successfully Set");
         }
         else
         {
         return response()->json("Not Set");
         }

    }
  
    public function SearchAutocomplete(Request $request)
    {
        $query = $request->input('query');
        $court = $request->input('courtID');
        $search = DB::table('tblper')
        ->where('fileNo', 'LIKE','%'.$query.'%')
        ->where('courtID', '=', $this->court)
        ->take(15)->get();
        $return_array = null;
        foreach($search as $s)
        {
          $return_array[]  =  ["value"=>$s->surname.' '.$s->first_name.' '.$s->othernames.' - '.$s->fileNo, "data"=>$s->fileNo];
        }   
        return response()->json(array("suggestions"=>$return_array));
    }

   
    public function exportToExcel(Request $request)
    {
        $export = $request['export'];
        if($export == 'Export To Excel')
        {


       
        if ($this->court != '' && $this->sess_division != '' && $this->sess_grade != '' && $this->sess_section != '')
        {
        $results = DB::table('tblper')
        ->join('tbl_court','tbl_court.id','=','tblper.courtID')
        ->leftJoin('tbldesignation','tbldesignation.id','=','tblper.Designation')
        ->leftJoin('tbldepartment','tbldepartment.id','=','tblper.section')
        ->join('tbldivision','tbldivision.divisionID','=','tblper.divisionID')
        ->where('tblper.courtID','=',$this->court)
        ->where('tblper.section','=',$this->sess_section)
        ->where('tblper.divisionID','=',$this->sess_division)
        ->where('tblper.grade','=',$this->sess_grade)
        ->orderby('tblper.section')
        ->orderby('tblper.divisionID')
        ->orderby('tblper.grade')
         ->selectRaw('tblper.fileNo, tblper.first_name, tblper.surname, tblper.grade, tblper.step, tblper.othernames, tbldesignation.designation, tbldepartment.department, tbldivision.division, tbl_court.court_name')
        ->get();

        $filename = "stafflist.csv";
        
        $fp = fopen($filename,"w");
        $seperator = "";
        $comma = "";

        $seperator .= 'FileNo, NAME, GRADE, STEP, DESIGNATION, SECTION, DIVISION, COURT';
        $seperator .= "\n";
        fputs($fp,$seperator);

        if($results != "")
        {
        $seperator = "";
        $comma = "";

        foreach ($results as $val){
            //$name = str_replace( ',', '', $val->name );
        $name = $val->surname.' '.$val->first_name.' '.$val->othernames;
        $value1 = array($val->fileNo, $name, $val->grade, $val->step, $val->designation, $val->department, $val->division, $val->court_name);
        $value = implode(",",$value1);
        //$seperator .= $comma . '' .str_replace("", '""', $value);
        $seperator .= $value."\n";

        $comma = ",";
        }

        $seperator .= "\n";
        fputs($fp, $seperator);
        }

        fclose($fp);


        header("Content-type: application/csv");
        header("Content-Disposition: attachment; filename=$filename");
        readfile($filename);
        
        }
        elseif ($this->court != '' && $this->sess_division != '' && $this->sess_section == '')
        {
         $results = DB::table('tblper')
        ->join('tbl_court','tbl_court.id','=','tblper.courtID')
        ->leftJoin('tbldesignation','tbldesignation.id','=','tblper.Designation')
        ->leftJoin('tbldepartment','tbldepartment.id','=','tblper.section')
        ->join('tbldivision','tbldivision.divisionID','=','tblper.divisionID')
        ->where('tblper.courtID','=',$this->court)
        ->where('tblper.divisionID','=',$this->sess_division)
        ->orderby('tblper.section')
        ->orderby('tblper.divisionID')
        ->orderby('tblper.grade')
        ->selectRaw('tblper.fileNo, tblper.first_name, tblper.surname, tblper.grade, tblper.step, tblper.othernames, tbldesignation.designation, tbldepartment.department, tbldivision.division, tbl_court.court_name')
        ->get();

        //dd($results);

        $filename = "stafflist.csv";
        
        $fp = fopen($filename,"w");
        $seperator = "";
        $comma = "";

        $seperator .= 'FileNo, NAME, GRADE, STEP, DESIGNATION, SECTION, DIVISION, COURT';
        $seperator .= "\n";
        fputs($fp,$seperator);

        if($results != "")
        {
        $seperator = "";
        $comma = "";

        foreach ($results as $val){
            //$name = str_replace( ',', '', $val->name );
        $name = $val->surname.' '.$val->first_name.' '.$val->othernames;
        $value1 = array($val->fileNo, $name, $val->grade, $val->step, $val->designation, $val->department, $val->division, $val->court_name);
        $value = implode(",",$value1);
        //$seperator .= $comma . '' .str_replace("", '""', $value);
        $seperator .= $value."\n";

        $comma = ",";
        }

        $seperator .= "\n";
        fputs($fp, $seperator);
        }

        fclose($fp);


        header("Content-type: application/csv");
        header("Content-Disposition: attachment; filename=$filename");
        readfile($filename);
        
        
        }
        elseif ($this->court != '' && $this->sess_division != '' && $this->sess_section != '')
        {
        $results = DB::table('tblper')
        ->join('tbl_court','tbl_court.id','=','tblper.courtID')
        ->leftJoin('tbldesignation','tbldesignation.id','=','tblper.Designation')
        ->leftJoin('tbldepartment','tbldepartment.id','=','tblper.section')
        ->join('tbldivision','tbldivision.divisionID','=','tblper.divisionID')
        ->where('tblper.courtID','=',$this->court)
        ->where('tblper.section','=',$this->sess_section)
        ->where('tblper.divisionID','=',$this->sess_division)
        ->orderby('tblper.section')
        ->orderby('tblper.divisionID')
        ->orderby('tblper.grade')
         ->selectRaw('tblper.fileNo, tblper.first_name, tblper.surname, tblper.grade, tblper.step, tblper.othernames, tbldesignation.designation, tbldepartment.department, tbldivision.division, tbl_court.court_name')
        ->get();
        
        $filename = "stafflist.csv";
        
        $fp = fopen($filename,"w");
        $seperator = "";
        $comma = "";

        $seperator .= 'FileNo, NAME, GRADE, STEP, DESIGNATION, SECTION, DIVISION, COURT';
        $seperator .= "\n";
        fputs($fp,$seperator);

        if($results != "")
        {
        $seperator = "";
        $comma = "";

        foreach ($results as $val){
            //$name = str_replace( ',', '', $val->name );
        $name = $val->surname.' '.$val->first_name.' '.$val->othernames;
        $value1 = array($val->fileNo, $name, $val->grade, $val->step, $val->designation, $val->department, $val->division, $val->court_name);
        $value = implode(",",$value1);
        //$seperator .= $comma . '' .str_replace("", '""', $value);
        $seperator .= $value."\n";

        $comma = ",";
        }

        $seperator .= "\n";
        fputs($fp, $seperator);
        }

        fclose($fp);


        header("Content-type: application/csv");
        header("Content-Disposition: attachment; filename=$filename");
        readfile($filename);
        
        }
        elseif ($this->court != '' && $this->sess_division == '' && $this->sess_grade == '' && $this->sess_section == '')
        {
        $results = DB::table('tblper')
        ->join('tbl_court','tbl_court.id','=','tblper.courtID')
        ->leftJoin('tbldesignation','tbldesignation.id','=','tblper.Designation')
        ->leftJoin('tbldepartment','tbldepartment.id','=','tblper.section')
        ->join('tbldivision','tbldivision.divisionID','=','tblper.divisionID')
        ->where('tblper.courtID','=',$this->court)
        ->orderby('tblper.section')
        ->orderby('tblper.divisionID')
        ->orderby('tblper.grade')
         ->selectRaw('tblper.fileNo, tblper.first_name, tblper.surname, tblper.grade, tblper.step, tblper.othernames, tbldesignation.designation, tbldepartment.department, tbldivision.division, tbl_court.court_name')
        ->get();
       
       $filename = "stafflist.csv";
        
        $fp = fopen($filename,"w");
        $seperator = "";
        $comma = "";

        $seperator .= 'FileNo, NAME, GRADE, STEP, DESIGNATION, SECTION, DIVISION, COURT';
        $seperator .= "\n";
        fputs($fp,$seperator);

        if($results != "")
        {
        $seperator = "";
        $comma = "";

        foreach ($results as $val){
            //$name = str_replace( ',', '', $val->name );
        $name = $val->surname.' '.$val->first_name.' '.$val->othernames;
        $value1 = array($val->fileNo, $name, $val->grade, $val->step, $val->designation, $val->department, $val->division, $val->court_name);
        $value = implode(",",$value1);
        //$seperator .= $comma . '' .str_replace("", '""', $value);
        $seperator .= $value."\n";

        $comma = ",";
        }

        $seperator .= "\n";
        fputs($fp, $seperator);
        }

        fclose($fp);


        header("Content-type: application/csv");
        header("Content-Disposition: attachment; filename=$filename");
        readfile($filename);
         
        }
        elseif ($this->court != '' && $this->sess_division != '' && $this->sess_grade != '' && $this->sess_section == '' && $this->sess_fileNo == '')
        {
        $results = DB::table('tblper')
        ->join('tbl_court','tbl_court.id','=','tblper.courtID')
        ->leftJoin('tbldesignation','tbldesignation.id','=','tblper.Designation')
        ->leftJoin('tbldepartment','tbldepartment.id','=','tblper.section')
        ->join('tbldivision','tbldivision.divisionID','=','tblper.divisionID')
        ->where('tblper.courtID','=',$this->court)
        ->where('tblper.divisionID','=',$this->sess_division)
        ->where('tblper.grade','=',$this->sess_grade)
        ->orderby('tblper.section')
        ->orderby('tblper.divisionID')
        ->orderby('tblper.grade')
         ->selectRaw('tblper.fileNo, tblper.first_name, tblper.surname, tblper.grade, tblper.step, tblper.othernames, tbldesignation.designation, tbldepartment.department, tbldivision.division, tbl_court.court_name')
        ->get();
       
       $filename = "stafflist.csv";
        
        $fp = fopen($filename,"w");
        $seperator = "";
        $comma = "";

        $seperator .= 'FileNo, NAME, GRADE, STEP, DESIGNATION, SECTION, DIVISION, COURT';
        $seperator .= "\n";
        fputs($fp,$seperator);

        if($results != "")
        {
        $seperator = "";
        $comma = "";

        foreach ($results as $val){
            //$name = str_replace( ',', '', $val->name );
        $name = $val->surname.' '.$val->first_name.' '.$val->othernames;
        $value1 = array($val->fileNo, $name, $val->grade, $val->step, $val->designation, $val->department, $val->division, $val->court_name);
        $value = implode(",",$value1);
        //$seperator .= $comma . '' .str_replace("", '""', $value);
        $seperator .= $value."\n";

        $comma = ",";
        }

        $seperator .= "\n";
        fputs($fp, $seperator);
        }

        fclose($fp);


        header("Content-type: application/csv");
        header("Content-Disposition: attachment; filename=$filename");
        readfile($filename);
         
        }
        elseif ($this->court != '' && $this->sess_division == '' && $this->sess_grade != '' && $this->sess_section == '' && $this->sess_fileNo == '')
        {
        $results = DB::table('tblper')
        ->join('tbl_court','tbl_court.id','=','tblper.courtID')
        ->leftJoin('tbldesignation','tbldesignation.id','=','tblper.Designation')
        ->leftJoin('tbldepartment','tbldepartment.id','=','tblper.section')
        ->join('tbldivision','tbldivision.divisionID','=','tblper.divisionID')
        ->where('tblper.courtID','=',$this->court)
        ->where('tblper.grade','=',$this->sess_grade)
        ->orderby('tblper.section')
        ->orderby('tblper.divisionID')
        ->orderby('tblper.grade')
         ->selectRaw('tblper.fileNo, tblper.first_name, tblper.surname, tblper.grade, tblper.step, tblper.othernames, tbldesignation.designation, tbldepartment.department, tbldivision.division, tbl_court.court_name')
        ->get();
        
       
       $filename = "stafflist.csv";
        
        $fp = fopen($filename,"w");
        $seperator = "";
        $comma = "";

        $seperator .= 'FileNo, NAME, GRADE, STEP, DESIGNATION, SECTION, DIVISION, COURT';
        $seperator .= "\n";
        fputs($fp,$seperator);

        if($results != "")
        {
        $seperator = "";
        $comma = "";

        foreach ($results as $val){
            //$name = str_replace( ',', '', $val->name );
        $name = $val->surname.' '.$val->first_name.' '.$val->othernames;
        $value1 = array($val->fileNo, $name, $val->grade, $val->step, $val->department, $val->section, $val->division, $val->court_name);
        $value = implode(",",$value1);
        //$seperator .= $comma . '' .str_replace("", '""', $value);
        $seperator .= $value."\n";

        $comma = ",";
        }

        $seperator .= "\n";
        fputs($fp, $seperator);
        }

        fclose($fp);


        header("Content-type: application/csv");
        header("Content-Disposition: attachment; filename=$filename");
        readfile($filename);
         
        }
        else
        {
        $results = DB::table('tblper')
        ->join('tbl_court','tbl_court.id','=','tblper.courtID')
        ->leftJoin('tbldesignation','tbldesignation.id','=','tblper.Designation')
        ->leftJoin('tbldepartment','tbldepartment.id','=','tblper.section')
        ->join('tbldivision','tbldivision.divisionID','=','tblper.divisionID')
        ->orderby('tblper.section')
        ->orderby('tblper.divisionID')
        ->orderby('tblper.grade')
        ->paginate(50);
       
       $filename = "stafflist.csv";
        
        $fp = fopen($filename,"w");
        $seperator = "";
        $comma = "";

        $seperator .= 'FileNo, NAME, GRADE, STEP, DESIGNATION, SECTION, DIVISION, COURT';
        $seperator .= "\n";
        fputs($fp,$seperator);

        if($results != "")
        {
        $seperator = "";
        $comma = "";

        foreach ($results as $val){
            //$name = str_replace( ',', '', $val->name );
        $name = $val->surname.' '.$val->first_name.' '.$val->othernames;
        $value1 = array($val->fileNo, $name, $val->grade, $val->step, $val->designation, $val->department, $val->division, $val->court_name);
        $value = implode(",",$value1);
        //$seperator .= $comma . '' .str_replace("", '""', $value);
        $seperator .= $value."\n";

        $comma = ",";
        }

        $seperator .= "\n";
        fputs($fp, $seperator);
        }

        fclose($fp);


        header("Content-type: application/csv");
        header("Content-Disposition: attachment; filename=$filename");
        readfile($filename);
      }

      }

      // start else if for first if statement
     /* elseif ($export == 'Export To PDF') {
        
        if ($this->court != '' && $this->sess_division != '' && $this->sess_grade != '' && $this->sess_section != '')
        {
        $results = DB::table('tblper')
        ->join('tbl_court','tbl_court.id','=','tblper.courtID')
        ->join('tbldesignation','tbldesignation.id','=','tblper.Designation')
        ->join('tbldepartment','tbldepartment.id','=','tblper.section')
        ->join('tbldivision','tbldivision.divisionID','=','tblper.divisionID')
        ->where('tblper.courtID','=',$this->court)
        ->where('tblper.section','=',$this->sess_section)
        ->where('tblper.divisionID','=',$this->sess_division)
        ->where('tblper.grade','=',$this->sess_grade)
        ->orderby('tblper.section')
        ->orderby('tblper.divisionID')
        ->orderby('tblper.grade')
         ->selectRaw('tblper.fileNo, tblper.first_name, tblper.surname, tblper.grade, tblper.step, tblper.othernames, tbldesignation.designation, tbldepartment.department, tbldivision.division, tbl_court.court_name')
        ->get();

        //$filename = "stafflist.csv";
        
         //$pdf = new Fpdf();
         Fpdf::AddPage();
         Fpdf::SetFont('Arial','B',18);
         Fpdf::Cell(0,10,"Title",0,"","C");
         Fpdf::Ln();
         Fpdf::Ln();
         Fpdf::SetFont('Arial','B',12);

         Fpdf::cell(35,8,"FileNo",1,"","C");
         Fpdf::cell(35,8,"NAME",1,"","L");
         Fpdf::cell(35,8,"GRADE",1,"","L");
         Fpdf::cell(35,8,"STEP",1,"","L");
         Fpdf::cell(35,8,"DESIGNATION",1,"","L");
         Fpdf::cell(35,8,"SECTION",1,"","L");
         Fpdf::cell(35,8,"DIVISION",1,"","L");
         Fpdf::cell(35,8,"COURT",1,"","L");

         Fpdf::Ln();
         Fpdf::SetFont("Arial","",10);
         foreach ($results as $val) {
         $name = $val->surname.' '.$val->first_name.' '.$val->othernames;
         Fpdf::cell(35,8,$al->fileNo,1,"","C");
         Fpdf::cell(35,8,$name,1,"","L");
         Fpdf::cell(35,8,$val->grade,1,"","L");
         Fpdf::cell(35,8,$val->step,1,"","L");
         Fpdf::cell(35,8,$val->designation,1,"","L");
         Fpdf::cell(35,8,$val->department,1,"","L");
         Fpdf::cell(35,8,$val->division,1,"","L");
         Fpdf::cell(35,8,$val->court_name,1,"","L");
         }
         Fpdf::Ln();
         Fpdf::Output();
         exit;
        }
        elseif ($this->court != '' && $this->sess_division != '' && $this->sess_section == '')
        {
         $results = DB::table('tblper')
        ->join('tbl_court','tbl_court.id','=','tblper.courtID')
        ->join('tbldesignation','tbldesignation.id','=','tblper.Designation')
        ->join('tbldepartment','tbldepartment.id','=','tblper.section')
        ->join('tbldivision','tbldivision.divisionID','=','tblper.divisionID')
        ->where('tblper.courtID','=',$this->court)
        ->where('tblper.divisionID','=',$this->sess_division)
        ->orderby('tblper.section')
        ->orderby('tblper.divisionID')
        ->orderby('tblper.grade')
        ->selectRaw('tblper.fileNo, tblper.first_name, tblper.surname, tblper.grade, tblper.step, tblper.othernames, tbldesignation.designation, tbldepartment.department, tbldivision.division, tbl_court.court_name')
        ->get();

        //dd($results);

        //$pdf = new Fpdf();
         Fpdf::AddPage();
         Fpdf::SetFont('Arial','B',18);
         Fpdf::Cell(0,10,"Title",0,"","C");
         Fpdf::Ln();
         Fpdf::Ln();
         Fpdf::SetFont('Arial','B',12);

         Fpdf::cell(35,8,"FileNo",1,"","C");
         Fpdf::cell(35,8,"NAME",1,"","L");
         Fpdf::cell(35,8,"GRADE",1,"","L");
         Fpdf::cell(35,8,"STEP",1,"","L");
         Fpdf::cell(35,8,"DESIGNATION",1,"","L");
         Fpdf::cell(35,8,"SECTION",1,"","L");
         Fpdf::cell(35,8,"DIVISION",1,"","L");
         Fpdf::cell(35,8,"COURT",1,"","L");

         Fpdf::Ln();
         Fpdf::SetFont("Arial","",10);
         foreach ($results as $val) {
         $name = $val->surname.' '.$val->first_name.' '.$val->othernames;
         Fpdf::cell(35,8,$al->fileNo,1,"","C");
         Fpdf::cell(35,8,$name,1,"","L");
         Fpdf::cell(35,8,$val->grade,1,"","L");
         Fpdf::cell(35,8,$val->step,1,"","L");
         Fpdf::cell(35,8,$val->designation,1,"","L");
         Fpdf::cell(35,8,$val->department,1,"","L");
         Fpdf::cell(35,8,$val->division,1,"","L");
         Fpdf::cell(35,8,$val->court_name,1,"","L");
         }
         Fpdf::Ln();
         Fpdf::Output();
         exit;
        }
        elseif ($this->court != '' && $this->sess_division != '' && $this->sess_section != '')
        {
        $results = DB::table('tblper')
        ->join('tbl_court','tbl_court.id','=','tblper.courtID')
        ->join('tbldesignation','tbldesignation.id','=','tblper.Designation')
        ->join('tbldepartment','tbldepartment.id','=','tblper.section')
        ->join('tbldivision','tbldivision.divisionID','=','tblper.divisionID')
        ->where('tblper.courtID','=',$this->court)
        ->where('tblper.section','=',$this->sess_section)
        ->where('tblper.divisionID','=',$this->sess_division)
        ->orderby('tblper.section')
        ->orderby('tblper.divisionID')
        ->orderby('tblper.grade')
         ->selectRaw('tblper.fileNo, tblper.first_name, tblper.surname, tblper.grade, tblper.step, tblper.othernames, tbldesignation.designation, tbldepartment.department, tbldivision.division, tbl_court.court_name')
        ->get();
        
        //$pdf = new Fpdf();
         Fpdf::AddPage();
         Fpdf::SetFont('Arial','B',18);
         Fpdf::Cell(0,10,"Title",0,"","C");
         Fpdf::Ln();
         Fpdf::Ln();
         Fpdf::SetFont('Arial','B',12);

         Fpdf::cell(35,8,"FileNo",1,"","C");
         Fpdf::cell(35,8,"NAME",1,"","L");
         Fpdf::cell(35,8,"GRADE",1,"","L");
         Fpdf::cell(35,8,"STEP",1,"","L");
         Fpdf::cell(35,8,"DESIGNATION",1,"","L");
         Fpdf::cell(35,8,"SECTION",1,"","L");
         Fpdf::cell(35,8,"DIVISION",1,"","L");
         Fpdf::cell(35,8,"COURT",1,"","L");

         Fpdf::Ln();
         Fpdf::SetFont("Arial","",10);
         foreach ($results as $val) {
         $name = $val->surname.' '.$val->first_name.' '.$val->othernames;
         Fpdf::cell(35,8,$al->fileNo,1,"","C");
         Fpdf::cell(35,8,$name,1,"","L");
         Fpdf::cell(35,8,$val->grade,1,"","L");
         Fpdf::cell(35,8,$val->step,1,"","L");
         Fpdf::cell(35,8,$val->designation,1,"","L");
         Fpdf::cell(35,8,$val->department,1,"","L");
         Fpdf::cell(35,8,$val->division,1,"","L");
         Fpdf::cell(35,8,$val->court_name,1,"","L");
         }
         Fpdf::Ln();
         Fpdf::Output();
         exit;        
        }
        elseif ($this->court != '' && $this->sess_division == '' && $this->sess_grade == '' && $this->sess_section == '')
        {
        $results = DB::table('tblper')
        ->join('tbl_court','tbl_court.id','=','tblper.courtID')
        ->join('tbldesignation','tbldesignation.id','=','tblper.Designation')
        ->join('tbldepartment','tbldepartment.id','=','tblper.section')
        ->join('tbldivision','tbldivision.divisionID','=','tblper.divisionID')
        ->where('tblper.courtID','=',$this->court)
        ->orderby('tblper.section')
        ->orderby('tblper.divisionID')
        ->orderby('tblper.grade')
         ->selectRaw('tblper.fileNo, tblper.first_name, tblper.surname, tblper.grade, tblper.step, tblper.othernames, tbldesignation.designation, tbldepartment.department, tbldivision.division, tbl_court.court_name')
        ->get();
       
       //$pdf = new Fpdf();
         Fpdf::AddPage();
         Fpdf::SetFont('Arial','B',18);
         Fpdf::Cell(0,10,"Title",0,"","C");
         Fpdf::Ln();
         Fpdf::Ln();
         Fpdf::SetFont('Arial','B',12);

         Fpdf::cell(35,8,"FileNo",1,"","C");
         Fpdf::cell(35,8,"NAME",1,"","L");
         Fpdf::cell(35,8,"GRADE",1,"","L");
         Fpdf::cell(35,8,"STEP",1,"","L");
         Fpdf::cell(35,8,"DESIGNATION",1,"","L");
         Fpdf::cell(35,8,"SECTION",1,"","L");
         Fpdf::cell(35,8,"DIVISION",1,"","L");
         Fpdf::cell(35,8,"COURT",1,"","L");

         Fpdf::Ln();
         Fpdf::SetFont("Arial","",10);
         foreach ($results as $val) {
         $name = $val->surname.' '.$val->first_name.' '.$val->othernames;
         Fpdf::cell(35,8,$al->fileNo,1,"","C");
         Fpdf::cell(35,8,$name,1,"","L");
         Fpdf::cell(35,8,$val->grade,1,"","L");
         Fpdf::cell(35,8,$val->step,1,"","L");
         Fpdf::cell(35,8,$val->designation,1,"","L");
         Fpdf::cell(35,8,$val->department,1,"","L");
         Fpdf::cell(35,8,$val->division,1,"","L");
         Fpdf::cell(35,8,$val->court_name,1,"","L");
         }
         Fpdf::Ln();
         Fpdf::Output();
         exit;      
        }
        elseif ($this->court != '' && $this->sess_division != '' && $this->sess_grade != '' && $this->sess_section == '' && $this->sess_fileNo == '')
        {
        $results = DB::table('tblper')
        ->join('tbl_court','tbl_court.id','=','tblper.courtID')
        ->join('tbldesignation','tbldesignation.id','=','tblper.Designation')
        ->join('tbldepartment','tbldepartment.id','=','tblper.section')
        ->join('tbldivision','tbldivision.divisionID','=','tblper.divisionID')
        ->where('tblper.courtID','=',$this->court)
        ->where('tblper.divisionID','=',$this->sess_division)
        ->where('tblper.grade','=',$this->sess_grade)
        ->orderby('tblper.section')
        ->orderby('tblper.divisionID')
        ->orderby('tblper.grade')
         ->selectRaw('tblper.fileNo, tblper.first_name, tblper.surname, tblper.grade, tblper.step, tblper.othernames, tbldesignation.designation, tbldepartment.department, tbldivision.division, tbl_court.court_name')
        ->get();
       
       //$pdf = new Fpdf();
         Fpdf::AddPage();
         Fpdf::SetFont('Arial','B',18);
         Fpdf::Cell(0,10,"Title",0,"","C");
         Fpdf::Ln();
         Fpdf::Ln();
         Fpdf::SetFont('Arial','B',12);

         Fpdf::cell(35,8,"FileNo",1,"","C");
         Fpdf::cell(35,8,"NAME",1,"","L");
         Fpdf::cell(35,8,"GRADE",1,"","L");
         Fpdf::cell(35,8,"STEP",1,"","L");
         Fpdf::cell(35,8,"DESIGNATION",1,"","L");
         Fpdf::cell(35,8,"SECTION",1,"","L");
         Fpdf::cell(35,8,"DIVISION",1,"","L");
         Fpdf::cell(35,8,"COURT",1,"","L");

         Fpdf::Ln();
         Fpdf::SetFont("Arial","",10);
         foreach ($results as $val) {
         $name = $val->surname.' '.$val->first_name.' '.$val->othernames;
         Fpdf::cell(35,8,$al->fileNo,1,"","C");
         Fpdf::cell(35,8,$name,1,"","L");
         Fpdf::cell(35,8,$val->grade,1,"","L");
         Fpdf::cell(35,8,$val->step,1,"","L");
         Fpdf::cell(35,8,$val->designation,1,"","L");
         Fpdf::cell(35,8,$val->department,1,"","L");
         Fpdf::cell(35,8,$val->division,1,"","L");
         Fpdf::cell(35,8,$val->court_name,1,"","L");
         }
         Fpdf::Ln();
         Fpdf::Output();
         exit;         
        }
        elseif ($this->court != '' && $this->sess_division == '' && $this->sess_grade != '' && $this->sess_section == '' && $this->sess_fileNo == '')
        {
        $results = DB::table('tblper')
        ->join('tbl_court','tbl_court.id','=','tblper.courtID')
        ->join('tbldesignation','tbldesignation.id','=','tblper.Designation')
        ->join('tbldepartment','tbldepartment.id','=','tblper.section')
        ->join('tbldivision','tbldivision.divisionID','=','tblper.divisionID')
        ->where('tblper.courtID','=',$this->court)
        ->where('tblper.grade','=',$this->sess_grade)
        ->orderby('tblper.section')
        ->orderby('tblper.divisionID')
        ->orderby('tblper.grade')
         ->selectRaw('tblper.fileNo, tblper.first_name, tblper.surname, tblper.grade, tblper.step, tblper.othernames, tbldesignation.designation, tbldepartment.department, tbldivision.division, tbl_court.court_name')
        ->get();
        
       
       //$pdf = new Fpdf();
         Fpdf::AddPage();
         Fpdf::SetFont('Arial','B',18);
         Fpdf::Cell(0,10,"Title",0,"","C");
         Fpdf::Ln();
         Fpdf::Ln();
         Fpdf::SetFont('Arial','B',12);

         Fpdf::cell(35,8,"FileNo",1,"","C");
         Fpdf::cell(35,8,"NAME",1,"","L");
         Fpdf::cell(35,8,"GRADE",1,"","L");
         Fpdf::cell(35,8,"STEP",1,"","L");
         Fpdf::cell(35,8,"DESIGNATION",1,"","L");
         Fpdf::cell(35,8,"SECTION",1,"","L");
         Fpdf::cell(35,8,"DIVISION",1,"","L");
         Fpdf::cell(35,8,"COURT",1,"","L");

         Fpdf::Ln();
         Fpdf::SetFont("Arial","",10);
         foreach ($results as $val) {
         $name = $val->surname.' '.$val->first_name.' '.$val->othernames;
         Fpdf::cell(35,8,$al->fileNo,1,"","C");
         Fpdf::cell(35,8,$name,1,"","L");
         Fpdf::cell(35,8,$val->grade,1,"","L");
         Fpdf::cell(35,8,$val->step,1,"","L");
         Fpdf::cell(35,8,$val->designation,1,"","L");
         Fpdf::cell(35,8,$val->department,1,"","L");
         Fpdf::cell(35,8,$val->division,1,"","L");
         Fpdf::cell(35,8,$val->court_name,1,"","L");
         }
         Fpdf::Ln();
         Fpdf::Output();
         exit;
        }
        else
        {
        $results = DB::table('tblper')
        ->join('tbl_court','tbl_court.id','=','tblper.courtID')
        ->join('tbldesignation','tbldesignation.id','=','tblper.Designation')
        ->join('tbldepartment','tbldepartment.id','=','tblper.section')
        ->join('tbldivision','tbldivision.divisionID','=','tblper.divisionID')
        ->orderby('tblper.section')
        ->orderby('tblper.divisionID')
        ->orderby('tblper.grade')
        ->paginate(50);
       
       //$pdf = new Fpdf();
         Fpdf::AddPage();
         Fpdf::SetFont('Arial','B',18);
         Fpdf::Cell(0,10,"Title",0,"","C");
         Fpdf::Ln();
         Fpdf::Ln();
         Fpdf::SetFont('Arial','B',12);

         Fpdf::cell(35,8,"FileNo",1,"","C");
         Fpdf::cell(35,8,"NAME",1,"","L");
         Fpdf::cell(35,8,"GRADE",1,"","L");
         Fpdf::cell(35,8,"STEP",1,"","L");
         Fpdf::cell(35,8,"DESIGNATION",1,"","L");
         Fpdf::cell(35,8,"SECTION",1,"","L");
         Fpdf::cell(35,8,"DIVISION",1,"","L");
         Fpdf::cell(35,8,"COURT",1,"","L");

         Fpdf::Ln();
         Fpdf::SetFont("Arial","",10);
         foreach ($results as $val) {
         $name = $val->surname.' '.$val->first_name.' '.$val->othernames;
         Fpdf::cell(35,8,$al->fileNo,1,"","C");
         Fpdf::cell(35,8,$name,1,"","L");
         Fpdf::cell(35,8,$val->grade,1,"","L");
         Fpdf::cell(35,8,$val->step,1,"","L");
         Fpdf::cell(35,8,$val->designation,1,"","L");
         Fpdf::cell(35,8,$val->department,1,"","L");
         Fpdf::cell(35,8,$val->division,1,"","L");
         Fpdf::cell(35,8,$val->court_name,1,"","L");
         }
         Fpdf::Ln();
         Fpdf::Output();
         exit;
      }

       } *///end if else
        
    }

   
    public function nhf()
    {
        $data['courts'] = DB::table('tbl_court')->get();
        $data['division'] = DB::table('tbldivision')->where('courtID','=',$this->court)->get();
        $data['department'] = DB::table('tbldepartment')->where('courtID','=',$this->court)->get();
        $data['staff'] = DB::table('tblper')
        ->join('tbl_court','tbl_court.id','=','tblper.courtID')
        ->join('tbldesignation','tbldesignation.id','=','tblper.Designation')
        ->join('tbldepartment','tbldepartment.id','=','tblper.section')
        ->join('tbldivision','tbldivision.divisionID','=','tblper.divisionID')
        ->paginate(50);
        return view('staffReports/nhf', $data);
    }

    
    public function searchNHF(Request $request)
    {
        $data['courts'] = DB::table('tbl_court')->get();
         $data['division'] = DB::table('tbldivision')->where('courtID','=',$this->court)->get();
        $data['department'] = DB::table('tbldepartment')->where('courtID','=',$this->court)->get();
        $court    = $request['Court'];
        $division = $request['division'];
        $section  = $request['section'];
        $fileNo   = $request['fileNo'];
        $grade    = $request['grade'];


        //create sessions for searches

        Session::put('search_court', $court);
        Session::put('search_division', $division);
        Session::put('search_section', $section);
        Session::put('search_grade', $grade);
        Session::put('search_fileNo', $fileNo);

        //dd($section);

        if($fileNo != '' && $division == '' && $grade == '' && $section == '')
        {
            $data['staff'] = DB::table('tblper')
        ->join('tbl_court','tbl_court.id','=','tblper.courtID')
        ->join('tbldesignation','tbldesignation.id','=','tblper.Designation')
        ->join('tbldepartment','tbldepartment.id','=','tblper.section')
        ->join('tbldivision','tbldivision.divisionID','=','tblper.divisionID')
        ->where('tblper.fileNo','=',$fileNo)
        ->paginate(50);
        return view('staffReports/nhf',$data);
        }
        elseif ($court != '' && $division != '' && $grade != '' && $section != '')
        {
        $data['staff'] = DB::table('tblper')
        ->join('tbl_court','tbl_court.id','=','tblper.courtID')
        ->join('tbldesignation','tbldesignation.id','=','tblper.Designation')
        ->join('tbldepartment','tbldepartment.id','=','tblper.section')
        ->join('tbldivision','tbldivision.divisionID','=','tblper.divisionID')
        ->where('tblper.courtID','=',$court)
        ->where('tblper.section','=',$section)
        ->where('tblper.divisionID','=',$division)
        ->where('tblper.grade','=',$grade)
        ->orderby('tblper.section')
        ->orderby('tblper.divisionID')
        ->orderby('tblper.grade')
        ->paginate(50);
        return view('staffReports/nhf',$data);
        
        }
        elseif ($court != '' && $division != '' && $section == '')
        {
         $data['staff'] = DB::table('tblper')
        ->join('tbl_court','tbl_court.id','=','tblper.courtID')
        ->join('tbldesignation','tbldesignation.id','=','tblper.Designation')
        ->join('tbldepartment','tbldepartment.id','=','tblper.section')
        ->join('tbldivision','tbldivision.divisionID','=','tblper.divisionID')
        ->where('tblper.courtID','=', $court)
        ->where('tblper.divisionID','=',$division)
        ->orderby('tblper.section')
        ->orderby('tblper.divisionID')
        ->orderby('tblper.grade')
        ->paginate(50);
        return view('staffReports/nhf',$data);
        }
        elseif ($court != '' && $division != '' && $section != '')
        {
        $data['staff'] = DB::table('tblper')
        ->join('tbl_court','tbl_court.id','=','tblper.courtID')
        ->join('tbldesignation','tbldesignation.id','=','tblper.Designation')
        ->join('tbldepartment','tbldepartment.id','=','tblper.section')
        ->join('tbldivision','tbldivision.divisionID','=','tblper.divisionID')
        ->where('tblper.courtID','=',$court)
        ->where('tblper.section','=',$section)
        ->where('tblper.divisionID','=',$division)
        ->orderby('tblper.section')
        ->orderby('tblper.divisionID')
        ->orderby('tblper.grade')
        ->paginate(50);
        
        return view('staffReports/nhf',$data);
        }
        elseif ($court != '' && $division == '' && $grade == '' && $section == '')
        {
        $data['staff'] = DB::table('tblper')
        ->join('tbl_court','tbl_court.id','=','tblper.courtID')
        ->join('tbldesignation','tbldesignation.id','=','tblper.Designation')
        ->join('tbldepartment','tbldepartment.id','=','tblper.section')
        ->join('tbldivision','tbldivision.divisionID','=','tblper.divisionID')
        ->where('tblper.courtID','=',$court)
        
        ->paginate(50);
        return view('staffReports/nhf',$data);
        
        }
        elseif ($court != '' && $division != '' && $grade != '' && $section == '' && $fileNo == '')
        {
        $data['staff'] = DB::table('tblper')
        ->join('tbl_court','tbl_court.id','=','tblper.courtID')
        ->join('tbldesignation','tbldesignation.id','=','tblper.Designation')
        ->join('tbldepartment','tbldepartment.id','=','tblper.section')
        ->join('tbldivision','tbldivision.divisionID','=','tblper.divisionID')
        ->where('tblper.courtID','=',$court)
        ->where('tblper.grade','=',$grade)
        ->where('tblper.division','=',$division)
        ->paginate(50);
        return view('staffReports/nhf',$data);
        
        }
        elseif ($court != '' && $division == '' && $grade != '' && $section == '' && $fileNo == '')
        {
        $data['staff'] = DB::table('tblper')
        ->join('tbl_court','tbl_court.id','=','tblper.courtID')
        ->join('tbldesignation','tbldesignation.id','=','tblper.Designation')
        ->join('tbldepartment','tbldepartment.id','=','tblper.section')
        ->join('tbldivision','tbldivision.divisionID','=','tblper.divisionID')
        ->where('tblper.courtID','=',$court)
        ->where('tblper.grade','=',$grade)
        ->paginate(50);
        return view('staffReports/nhf',$data);
        
        }
        else
        {
        $data['staff'] = DB::table('tblper')
        ->join('tbl_court','tbl_court.id','=','tblper.courtID')
        ->join('tbldesignation','tbldesignation.id','=','tblper.Designation')
        ->join('tbldepartment','tbldepartment.id','=','tblper.section')
        ->join('tbldivision','tbldivision.divisionID','=','tblper.divisionID')
        ->orderby('tblper.section')
        ->orderby('tblper.divisionID')
        ->orderby('tblper.grade')
        ->paginate(50);
        return view('staffReports/nhf',$data);
        }
    }

    public function exportNHF(Request $request)
    {
        $export = $request['export'];
       
        if ($this->court != '' && $this->sess_division != '' && $this->sess_grade != '' && $this->sess_section != '')
        {
        $results = DB::table('tblper')
        ->join('tbl_court','tbl_court.id','=','tblper.courtID')
        ->join('tbldesignation','tbldesignation.id','=','tblper.Designation')
        ->join('tbldepartment','tbldepartment.id','=','tblper.section')
        ->join('tbldivision','tbldivision.divisionID','=','tblper.divisionID')
        ->where('tblper.courtID','=',$this->court)
        ->where('tblper.section','=',$this->sess_section)
        ->where('tblper.divisionID','=',$this->sess_division)
        ->where('tblper.grade','=',$this->sess_grade)
        ->orderby('tblper.section')
        ->orderby('tblper.divisionID')
        ->orderby('tblper.grade')
         ->selectRaw('tblper.fileNo, tblper.first_name, tblper.surname, tblper.grade, tblper.step, tblper.othernames, tbldesignation.designation, tbldepartment.department, tbldivision.division, tbl_court.court_name')
        ->get();

        $filename = "stafflist.csv";
        
        $fp = fopen($filename,"w");
        $seperator = "";
        $comma = "";

        $seperator .= 'FileNo, NAME, GRADE, STEP, DESIGNATION, SECTION, DIVISION, COURT';
        $seperator .= "\n";
        fputs($fp,$seperator);

        if($results != "")
        {
        $seperator = "";
        $comma = "";

        foreach ($results as $val){
            //$name = str_replace( ',', '', $val->name );
        $name = $val->surname.' '.$val->first_name.' '.$val->othernames;
        $value1 = array($val->fileNo, $name, $val->grade, $val->step, $val->designation, $val->department, $val->division, $val->court_name);
        $value = implode(",",$value1);
        //$seperator .= $comma . '' .str_replace("", '""', $value);
        $seperator .= $value."\n";

        $comma = ",";
        }

        $seperator .= "\n";
        fputs($fp, $seperator);
        }

        fclose($fp);


        header("Content-type: application/csv");
        header("Content-Disposition: attachment; filename=$filename");
        readfile($filename);
        
        }
        elseif ($this->court != '' && $this->sess_division != '' && $this->sess_section == '')
        {
         $results = DB::table('tblper')
        ->join('tbl_court','tbl_court.id','=','tblper.courtID')
        ->join('tbldesignation','tbldesignation.id','=','tblper.Designation')
        ->join('tbldepartment','tbldepartment.id','=','tblper.section')
        ->join('tbldivision','tbldivision.divisionID','=','tblper.divisionID')
        ->where('tblper.courtID','=',$this->court)
        ->where('tblper.divisionID','=',$this->sess_division)
        ->orderby('tblper.section')
        ->orderby('tblper.divisionID')
        ->orderby('tblper.grade')
        ->selectRaw('tblper.fileNo, tblper.first_name, tblper.surname, tblper.grade, tblper.step, tblper.othernames, tbldesignation.designation, tbldepartment.department, tbldivision.division, tbl_court.court_name')
        ->get();

        //dd($results);

        $filename = "stafflist.csv";
        
        $fp = fopen($filename,"w");
        $seperator = "";
        $comma = "";

        $seperator .= 'FileNo, NAME, GRADE, STEP, DESIGNATION, SECTION, DIVISION, COURT';
        $seperator .= "\n";
        fputs($fp,$seperator);

        if($results != "")
        {
        $seperator = "";
        $comma = "";

        foreach ($results as $val){
            //$name = str_replace( ',', '', $val->name );
        $name = $val->surname.' '.$val->first_name.' '.$val->othernames;
        $value1 = array($val->fileNo, $name, $val->grade, $val->step, $val->designation, $val->department, $val->division, $val->court_name);
        $value = implode(",",$value1);
        //$seperator .= $comma . '' .str_replace("", '""', $value);
        $seperator .= $value."\n";

        $comma = ",";
        }

        $seperator .= "\n";
        fputs($fp, $seperator);
        }

        fclose($fp);


        header("Content-type: application/csv");
        header("Content-Disposition: attachment; filename=$filename");
        readfile($filename);
        
        
        }
        elseif ($this->court != '' && $this->sess_division != '' && $this->sess_section != '')
        {
        $results = DB::table('tblper')
        ->join('tbl_court','tbl_court.id','=','tblper.courtID')
        ->join('tbldesignation','tbldesignation.id','=','tblper.Designation')
        ->join('tbldepartment','tbldepartment.id','=','tblper.section')
        ->join('tbldivision','tbldivision.divisionID','=','tblper.divisionID')
        ->where('tblper.courtID','=',$this->court)
        ->where('tblper.section','=',$this->sess_section)
        ->where('tblper.divisionID','=',$this->sess_division)
        ->orderby('tblper.section')
        ->orderby('tblper.divisionID')
        ->orderby('tblper.grade')
         ->selectRaw('tblper.fileNo, tblper.first_name, tblper.surname, tblper.grade, tblper.step, tblper.othernames, tbldesignation.designation, tbldepartment.department, tbldivision.division, tbl_court.court_name')
        ->get();
        
        $filename = "stafflist.csv";
        
        $fp = fopen($filename,"w");
        $seperator = "";
        $comma = "";

        $seperator .= 'FileNo, NAME, GRADE, STEP, DESIGNATION, SECTION, DIVISION, COURT';
        $seperator .= "\n";
        fputs($fp,$seperator);

        if($results != "")
        {
        $seperator = "";
        $comma = "";

        foreach ($results as $val){
            //$name = str_replace( ',', '', $val->name );
        $name = $val->surname.' '.$val->first_name.' '.$val->othernames;
        $value1 = array($val->fileNo, $name, $val->grade, $val->step, $val->designation, $val->department, $val->division, $val->court_name);
        $value = implode(",",$value1);
        //$seperator .= $comma . '' .str_replace("", '""', $value);
        $seperator .= $value."\n";

        $comma = ",";
        }

        $seperator .= "\n";
        fputs($fp, $seperator);
        }

        fclose($fp);


        header("Content-type: application/csv");
        header("Content-Disposition: attachment; filename=$filename");
        readfile($filename);
        
        }
        elseif ($this->court != '' && $this->sess_division == '' && $this->sess_grade == '' && $this->sess_section == '')
        {
        $results = DB::table('tblper')
        ->join('tbl_court','tbl_court.id','=','tblper.courtID')
        ->join('tbldesignation','tbldesignation.id','=','tblper.Designation')
        ->join('tbldepartment','tbldepartment.id','=','tblper.section')
        ->join('tbldivision','tbldivision.divisionID','=','tblper.divisionID')
        ->where('tblper.courtID','=',$this->court)
        ->orderby('tblper.section')
        ->orderby('tblper.divisionID')
        ->orderby('tblper.grade')
         ->selectRaw('tblper.fileNo, tblper.first_name, tblper.surname, tblper.grade, tblper.step, tblper.othernames, tbldesignation.designation, tbldepartment.department, tbldivision.division, tbl_court.court_name')
        ->get();
       
       $filename = "stafflist.csv";
        
        $fp = fopen($filename,"w");
        $seperator = "";
        $comma = "";

        $seperator .= 'FileNo, NAME, GRADE, STEP, DESIGNATION, SECTION, DIVISION, COURT';
        $seperator .= "\n";
        fputs($fp,$seperator);

        if($results != "")
        {
        $seperator = "";
        $comma = "";

        foreach ($results as $val){
            //$name = str_replace( ',', '', $val->name );
        $name = $val->surname.' '.$val->first_name.' '.$val->othernames;
        $value1 = array($val->fileNo, $name, $val->grade, $val->step, $val->designation, $val->department, $val->division, $val->court_name);
        $value = implode(",",$value1);
        //$seperator .= $comma . '' .str_replace("", '""', $value);
        $seperator .= $value."\n";

        $comma = ",";
        }

        $seperator .= "\n";
        fputs($fp, $seperator);
        }

        fclose($fp);


        header("Content-type: application/csv");
        header("Content-Disposition: attachment; filename=$filename");
        readfile($filename);
         
        }
        elseif ($this->court != '' && $this->sess_division != '' && $this->sess_grade != '' && $this->sess_section == '' && $this->sess_fileNo == '')
        {
        $results = DB::table('tblper')
        ->join('tbl_court','tbl_court.id','=','tblper.courtID')
        ->join('tbldesignation','tbldesignation.id','=','tblper.Designation')
        ->join('tbldepartment','tbldepartment.id','=','tblper.section')
        ->join('tbldivision','tbldivision.divisionID','=','tblper.divisionID')
        ->where('tblper.courtID','=',$this->court)
        ->where('tblper.divisionID','=',$this->sess_division)
        ->where('tblper.grade','=',$this->sess_grade)
        ->orderby('tblper.section')
        ->orderby('tblper.divisionID')
        ->orderby('tblper.grade')
         ->selectRaw('tblper.fileNo, tblper.first_name, tblper.surname, tblper.grade, tblper.step, tblper.othernames, tbldesignation.designation, tbldepartment.department, tbldivision.division, tbl_court.court_name')
        ->get();
       
       $filename = "stafflist.csv";
        
        $fp = fopen($filename,"w");
        $seperator = "";
        $comma = "";

        $seperator .= 'FileNo, NAME, GRADE, STEP, DESIGNATION, SECTION, DIVISION, COURT';
        $seperator .= "\n";
        fputs($fp,$seperator);

        if($results != "")
        {
        $seperator = "";
        $comma = "";

        foreach ($results as $val){
            //$name = str_replace( ',', '', $val->name );
        $name = $val->surname.' '.$val->first_name.' '.$val->othernames;
        $value1 = array($val->fileNo, $name, $val->grade, $val->step, $val->designation, $val->department, $val->division, $val->court_name);
        $value = implode(",",$value1);
        //$seperator .= $comma . '' .str_replace("", '""', $value);
        $seperator .= $value."\n";

        $comma = ",";
        }

        $seperator .= "\n";
        fputs($fp, $seperator);
        }

        fclose($fp);


        header("Content-type: application/csv");
        header("Content-Disposition: attachment; filename=$filename");
        readfile($filename);
         
        }
        elseif ($this->court != '' && $this->sess_division == '' && $this->sess_grade != '' && $this->sess_section == '' && $this->sess_fileNo == '')
        {
        $results = DB::table('tblper')
        ->join('tbl_court','tbl_court.id','=','tblper.courtID')
        ->join('tbldesignation','tbldesignation.id','=','tblper.Designation')
        ->join('tbldepartment','tbldepartment.id','=','tblper.section')
        ->join('tbldivision','tbldivision.divisionID','=','tblper.divisionID')
        ->where('tblper.courtID','=',$this->court)
        ->where('tblper.grade','=',$this->sess_grade)
        ->orderby('tblper.section')
        ->orderby('tblper.divisionID')
        ->orderby('tblper.grade')
         ->selectRaw('tblper.fileNo, tblper.first_name, tblper.surname, tblper.grade, tblper.step, tblper.othernames, tbldesignation.designation, tbldepartment.department, tbldivision.division, tbl_court.court_name')
        ->get();
        
       
       $filename = "stafflist.csv";
        
        $fp = fopen($filename,"w");
        $seperator = "";
        $comma = "";

        $seperator .= 'FileNo, NAME, GRADE, STEP, DESIGNATION, SECTION, DIVISION, COURT';
        $seperator .= "\n";
        fputs($fp,$seperator);

        if($results != "")
        {
        $seperator = "";
        $comma = "";

        foreach ($results as $val){
            //$name = str_replace( ',', '', $val->name );
        $name = $val->surname.' '.$val->first_name.' '.$val->othernames;
        $value1 = array($val->fileNo, $name, $val->grade, $val->step, $val->department, $val->section, $val->division, $val->court_name);
        $value = implode(",",$value1);
        //$seperator .= $comma . '' .str_replace("", '""', $value);
        $seperator .= $value."\n";

        $comma = ",";
        }

        $seperator .= "\n";
        fputs($fp, $seperator);
        }

        fclose($fp);


        header("Content-type: application/csv");
        header("Content-Disposition: attachment; filename=$filename");
        readfile($filename);
         
        }
        else
        {
        $results = DB::table('tblper')
        ->join('tbl_court','tbl_court.id','=','tblper.courtID')
        ->join('tbldesignation','tbldesignation.id','=','tblper.Designation')
        ->join('tbldepartment','tbldepartment.id','=','tblper.section')
        ->join('tbldivision','tbldivision.divisionID','=','tblper.divisionID')
        ->orderby('tblper.section')
        ->orderby('tblper.divisionID')
        ->orderby('tblper.grade')
        ->paginate(50);
       
       $filename = "stafflist.csv";
        
        $fp = fopen($filename,"w");
        $seperator = "";
        $comma = "";

        $seperator .= 'FileNo, NAME, GRADE, STEP, DESIGNATION, SECTION, DIVISION, COURT';
        $seperator .= "\n";
        fputs($fp,$seperator);

        if($results != "")
        {
        $seperator = "";
        $comma = "";

        foreach ($results as $val){
            //$name = str_replace( ',', '', $val->name );
        $name = $val->surname.' '.$val->first_name.' '.$val->othernames;
        $value1 = array($val->fileNo, $name, $val->grade, $val->step, $val->designation, $val->department, $val->division, $val->court_name);
        $value = implode(",",$value1);
        //$seperator .= $comma . '' .str_replace("", '""', $value);
        $seperator .= $value."\n";

        $comma = ",";
        }

        $seperator .= "\n";
        fputs($fp, $seperator);
        }

        fclose($fp);


        header("Content-type: application/csv");
        header("Content-Disposition: attachment; filename=$filename");
        readfile($filename);
      }

      

    }
}
