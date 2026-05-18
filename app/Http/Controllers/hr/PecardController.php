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
use File;
use App\Http\Controllers\Controller;
use Session;
class PecardController extends ParentController
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
public $divisionID; 

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
    $data['CourtInfo']=$this->CourtInfo();
    if($data['CourtInfo']->courtstatus==0){$request['court']=$data['CourtInfo']->courtid;}
    if($data['CourtInfo']->divisionstatus==0){$request['division']=$data['CourtInfo']->divisionid;}

    if($data['CourtInfo'])
  {
      $data['staff'] = DB::table('tblper')->where('courtID','=',$data['CourtInfo']->courtid)->get();
  }
  else{
      $data['staff'] = DB::table('tblper')->where('courtID','=',$data['CourtInfo']->courtid)->get();
  }

	return view('pecard.index',$data);    
}

public function createIndex()
  {
      $data['CourtInfo']=$this->CourtInfo();
      if($data['CourtInfo']->courtstatus==0){$request['court']=$data['CourtInfo']->courtid;}
      if($data['CourtInfo']->divisionstatus==0){$request['division']=$data['CourtInfo']->divisionid;}

        $divisionID = $this->divisionID;
        $divisionsession = session('divsession');
        $data['division'] =  DB::table('tbldivision')->where('courtID','=', session('anycourt'))->get();
        $data['court'] = DB::table('tbl_court')->get();
        $data['users'] = DB::table('tblper')
        //->where('tblper.divisionID', '=', $divisionID)
         //->where('courtID','=', $data['CourtInfo']->courtid)
        ->orderBy('surname', 'Asc')
        ->get();

      return view('pecard.test',$data);    
  }


public function peNames(Request $request)
{
	$year = trim($request->input('year'));
	$bank = trim($request->input('bankName'));
	$bankGroup = trim($request->input('bankGroup'));
	$data['bankgroup']=$bankGroup;
	$division = $this->division;
	$this->validate($request,[       
		'year' => 'required|integer', 
		'bankName' => 'required|regex:/^[\pL\s\-]+$/u', 
		'bankGroup' => 'required|integer'   
		]);
	//DB::enableQueryLog();

        /*$data['users'] = DB::table('tblpayment')
		->select('fileNo', 'name', 'year', 'bankGroup')
		->where('bank', $bank)
		->where('bankGroup', $bankGroup)
		->where('division', $division)
		->where('year', $year)
		->orderBy('fileNo', 'Asc')
		->groupBy('fileNo')
		->get();*/

                $data['users'] = DB::table('tblper')
		->join('tblbanklist', 'tblbanklist.bankID', '=', 'tblper.bankID')	
		->where('tblbanklist.bank', $bank)
		->where('tblper.bankGroup', $bankGroup)
		->where('tblper.divisionID', $this->divisionID)
		->where('tblper.employee_type', '<>', 'CONSOLIDATED')
		->where('tblper.staff_status', '=', 1)

		->get();

                $data['year']=$year;           

	/*$data['users'] = DB::select('select fileNo, name, year from tblpayment where bank = ? and bankGroup = ? and division = ? and year = ? group by fileNo', [$bank, $bankGroup, $division, $year]);*/
	//dd(DB::getQueryLog());
	//dd($data['users']);
	$data['bankName']=$bank;
	//Session::put('peyear', $year);
	return view('pecard.peCardNames', $data);
}
public function peReport($fileno, $year)
{
	$data['details'] = DB::table('tblper')
				->join('tblbanklist', 'tblper.bankID', '=', 'tblbanklist.bankID')
				->select('fileNo', 'surname', 'first_name', 'othernames', 'rank', 'grade', 'step', 'bank', 'AccNo',
					'appointment_date', 'dob', 'home_address', 'employee_type')
				->where('fileno', '=', $fileno)
				->first();
				
	$data['getLevel'] = DB::table('tblpayment')
	->where('fileno', '=', $fileno)
	->where('year', '=', $year)
	->orderBy('date','DESC')
	->first();

	$arrears = DB::table('tblarrears')
	->where('year', '=', $year)
	->where('fileno', '=', $fileno)
	->get();
	$arr = array();
	$app = array();
	foreach ($arrears as $key) {
		if($key->type == 'new-appointment')
			$app[] = $key->month;
		else
			$arr[] = $key->month;
	}
	$data['year'] = $year;
	$data['arr'] = $arr;
	$data['app'] = $app;
	//dd($arr);
	//DB::enableQueryLog();
	$query = DB::table('tblper')
	->select('month', 'basic_salary','actingAllow','arrearsBasic','tax','pension','nhf','unionDues','ugv','nicncoop','ctlsLab','ctlsFed','fedhousing','surcharge','bicycleAdv','cumEmolu','motorBasicAll','employee_type','phoneCharges','pa_deduct','totalDeduct','netpay','grosspay','totalEmolu','callDuty','hazard')
	->join('tblpayment', 'tblpayment.fileNo', '=','tblper.fileNo')
	->where('tblper.fileno', '=', $fileno)
	->where('tblpayment.year', '=', $year)
	->get();
	//dd(DB::getQueryLog());
	//dd($query);
	$result = array();
	foreach ($query as  $value) 
	{
		$month = $value->month;
		$result[$month]['basic_salary'] = $value->basic_salary;
		//	var_dump($value);
		$result[$month]['actingAllow'] = $value->actingAllow;
		$result[$month]['arrearsBasic'] = $value->arrearsBasic;
		$result[$month]['tax'] = $value->tax;
		$result[$month]['pension'] = $value->pension;
		$result[$month]['nhf'] = $value->nhf;
		$result[$month]['unionDues'] = $value->unionDues;
		$result[$month]['ugv'] = $value->ugv;
		$result[$month]['nicncoop'] = $value->nicncoop;
		$result[$month]['ctlsLab'] = $value->ctlsLab;
		$result[$month]['ctlsFed'] = $value->ctlsFed;
		$result[$month]['fedhousing'] = $value->fedhousing;
		$result[$month]['surcharge'] = $value->surcharge;
		$result[$month]['bicycleAdv'] = $value->bicycleAdv;
		$result[$month]['cumEmolu'] = $value->cumEmolu;
		$result[$month]['motorBasicAll'] = $value->motorBasicAll;
                $result[$month]['callDuty'] = $value->callDuty;
		$result[$month]['hazard'] = $value->hazard;
		$result[$month]['phoneCharges'] = $value->phoneCharges;
		$result[$month]['pa_deduct'] = $value->pa_deduct;
		$result[$month]['totalDeduct'] = $value->totalDeduct;
		$result[$month]['netpay'] = $value->netpay;
		$result[$month]['grosspay'] = $value->grosspay;
		$result[$month]['totalEmolu'] = $value->totalEmolu;
	//var_dump($result);
	}
	$fullmonth=array("JANUARY","FEBRUARY","MARCH","APRIL","MAY","JUNE","JULY", "AUGUST","SEPTEMBER","OCTOBER","NOVEMBER", "DECEMBER" );
	$rowcount=0;
	$empty=0.0;
	for ($row = 0; $row <=11; $row++) 
	{
		$currentmonth=$fullmonth[$row];
		if (!isset($result[$currentmonth]['basic_salary']))
		{
			$result[$currentmonth]['basic_salary'] = $empty;
			$result[$currentmonth]['actingAllow'] = $empty;
			$result[$currentmonth]['arrearsBasic'] = $empty;
			$result[$currentmonth]['tax'] = $empty;
			$result[$currentmonth]['pension'] = $empty;
			$result[$currentmonth]['nhf'] = $empty;
			$result[$currentmonth]['unionDues'] = $empty;
			$result[$currentmonth]['ugv'] = $empty;
			$result[$currentmonth]['nicncoop'] = $empty;
			$result[$currentmonth]['ctlsLab'] = $empty;
			$result[$currentmonth]['ctlsFed'] = $empty;
			$result[$currentmonth]['fedhousing'] = $empty;
			$result[$currentmonth]['surcharge'] = $empty;
			$result[$currentmonth]['bicycleAdv'] = $empty;
			$result[$currentmonth]['cumEmolu'] = $empty;
			$result[$currentmonth]['motorBasicAll'] = $empty;
                        
                        $result[$currentmonth]['callDuty'] = $empty;
		    $result[$currentmonth]['hazard'] = $empty;

			$result[$currentmonth]['phoneCharges'] = $empty;
			$result[$currentmonth]['pa_deduct'] = $empty;
			$result[$currentmonth]['totalDeduct'] =$empty;
			$result[$currentmonth]['netpay'] = $empty;
			$result[$currentmonth]['grosspay'] = $empty;
			$result[$currentmonth]['totalEmolu'] = $empty;
		} 
	}
	$imageNewName        = $fileno . '.jpg';
	$path                = base_path() . '/public/passport/';
	if(File::exists(base_path() . '/public/passport/' . $imageNewName )) //check folder
	{
		$user_picture = $imageNewName;
	}else{
		$user_picture =  '0.png';
	}
	$data['image'] = $user_picture;
	$data['result'] = $result;
	
	return view('pecard.peReport', $data);
}

public function cardReport(Request $request)
{
    $fileno = $request['staff'];
    $year   = $request['year'];

    $data['details'] = DB::table('tblper')
        ->join('tblbanklist', 'tblper.bankID', '=', 'tblbanklist.bankID')
        ->select('fileNo', 'surname', 'first_name', 'othernames', 'rank', 'grade', 'step', 'bank', 'AccNo',
            'appointment_date', 'dob', 'home_address', 'employee_type')
        ->where('fileno', '=', $fileno)
        ->first();

    $data['getLevel'] = DB::table('tblpayment')
        ->where('fileno', '=', $fileno)
        ->where('year', '=', $year)
        ->orderBy('date','DESC')
        ->first();

    $arrears = DB::table('tblarrears')
        ->where('year', '=', $year)
        ->where('fileno', '=', $fileno)
        ->get();
    $arr = array();
    $app = array();
    foreach ($arrears as $key) {
        if($key->type == 'new-appointment')
            $app[] = $key->month;
        else
            $arr[] = $key->month;
    }
    $data['year'] = $year;
    $data['arr'] = $arr;
    $data['app'] = $app;
    //dd($arr);
    //DB::enableQueryLog();
    $query = DB::table('tblper')
        //->select('month', 'basic_salary','actingAllow','arrearsBasic','tax','pension','nhf','unionDues','ugv','nicncoop','ctlsLab','ctlsFed','fedhousing','surcharge','bicycleAdv','cumEmolu','motorBasicAll','employee_type','phoneCharges','pa_deduct','totalDeduct','netpay','grosspay','totalEmolu','callDuty','hazard')
        ->join('tblpayment', 'tblpayment.fileNo', '=','tblper.fileNo')
        ->where('tblper.fileno', '=', $fileno)
        ->where('tblpayment.year', '=', $year)
        ->get();
    //dd(DB::getQueryLog());
    //dd($query);
    $result = array();
    foreach ($query as  $value)
    {
        $month = $value->month;
        $result[$month]['BS'] = $value->BS;
        //	var_dump($value);
        //$result[$month]['actingAllow'] = $value->actingAllow;
        $result[$month]['AEarn'] = $value->AEarn; //arrearsBasic
        $result[$month]['TAX'] = $value->TAX;
        $result[$month]['PEN'] = $value->PEN;
        $result[$month]['NHF'] = $value->NHF;
        $result[$month]['UD'] = $value->UD;
        //$result[$month]['ugv'] = $value->ugv;
        $result[$month]['HA'] = $value->HA;
        $result[$month]['ML'] = $value->ML;
        $result[$month]['TR'] = $value->TR;
        $result[$month]['FUR'] = $value->FUR;
        $result[$month]['LEAV'] = $value->LEAV;
        $result[$month]['TD'] = $value->TD;
        $result[$month]['NetPay'] = $value->NetPay;
        $result[$month]['TEarn'] = $value->TEarn;
        //var_dump($result);
    }
    $fullmonth=array("JANUARY","FEBRUARY","MARCH","APRIL","MAY","JUNE","JULY", "AUGUST","SEPTEMBER","OCTOBER","NOVEMBER", "DECEMBER" );
    $rowcount=0;
    $empty=0.0;
    for ($row = 0; $row <=11; $row++)
    {
        $currentmonth=$fullmonth[$row];
        if (!isset($result[$currentmonth]['basic_salary']))
        {
            $result[$currentmonth]['BA'] = $empty;
            $result[$currentmonth]['TEarn'] = $empty;
            $result[$currentmonth]['TAX'] = $empty;
            $result[$currentmonth]['PEN'] = $empty;
            $result[$currentmonth]['NHF'] = $empty;
            $result[$currentmonth]['UD'] = $empty;
            $result[$currentmonth]['HA'] = $empty;
            $result[$currentmonth]['ML'] = $empty;
            $result[$currentmonth]['TR'] = $empty;
            $result[$currentmonth]['FUR'] = $empty;
            $result[$currentmonth]['LEAV'] = $empty;
            $result[$currentmonth]['TD'] = $empty;

            $result[$currentmonth]['NetPay'] = $empty;
            $result[$currentmonth]['TEarn'] = $empty;


        }
    }
    $imageNewName        = $fileno . '.jpg';
    $path                = base_path() . '/public/passport/';
    if(File::exists(base_path() . '/public/passport/' . $imageNewName )) //check folder
    {
        $user_picture = $imageNewName;
    }else{
        $user_picture =  '0.png';
    }
    $data['image'] = $user_picture;
    $data['result'] = $result;
}

public function viewCard(Request $request)
{
    $fileno = $request['staffName'];
    $year   = $request['year'];

     $request->session()->flash('staff',$request['staffName']);
     $request->session()->flash('yr',$request['year']);


     $data['getLevel'] = DB::table('tblpayment')
        ->where('fileno', '=', $fileno)
        ->where('year', '=', $year)
        ->first();

       if(!$data['getLevel'])
      {
        return back()->with('err','No Record Found');
      }   

    $data['details'] = DB::table('tblper')
        ->join('tblbanklist', 'tblper.bankID', '=', 'tblbanklist.bankID')
        ->select('fileNo', 'surname', 'first_name', 'othernames', 'rank', 'grade', 'step', 'bank', 'AccNo',
            'appointment_date', 'dob', 'home_address', 'employee_type')
        ->where('fileno', '=', $fileno)
        ->first();

   

    $arrears = DB::table('tblarrears')
        ->where('year', '=', $year)
        ->where('fileno', '=', $fileno)
        ->get();
    $arr = array();
    $app = array();
    foreach ($arrears as $key) {
        if($key->type == 'new-appointment')
            $app[] = $key->month;
        else
            $arr[] = $key->month;
    }
    $data['year'] = $year;
    $data['arr'] = $arr;
    $data['app'] = $app;
    //dd($arr);
    //DB::enableQueryLog();
    $query = DB::table('tblper')
        //->select('month', 'basic_salary','actingAllow','arrearsBasic','tax','pension','nhf','unionDues','ugv','nicncoop','ctlsLab','ctlsFed','fedhousing','surcharge','bicycleAdv','cumEmolu','motorBasicAll','employee_type','phoneCharges','pa_deduct','totalDeduct','netpay','grosspay','totalEmolu','callDuty','hazard')
        ->join('tblpayment', 'tblpayment.fileNo', '=','tblper.fileNo')
        ->where('tblper.fileno', '=', $fileno)
        ->where('tblpayment.year', '=', $year)
        ->get();
    //dd(DB::getQueryLog());
    //dd($query);
    $result = array();
    foreach ($query as  $value)
    {
        $month = $value->month;
        $result[$month]['Bs'] = $value->Bs;
        //	var_dump($value);
        //$result[$month]['actingAllow'] = $value->actingAllow;
        $result[$month]['AEarn'] = $value->AEarn; //arrearsBasic
        $result[$month]['TAX'] = $value->TAX;
        $result[$month]['PEN'] = $value->PEN;
        $result[$month]['NHF'] = $value->NHF;
        $result[$month]['UD'] = $value->UD;
        //$result[$month]['ugv'] = $value->ugv;
        $result[$month]['HA'] = $value->HA;
        $result[$month]['ML'] = $value->ML;
        $result[$month]['TR'] = $value->TR;
        $result[$month]['FUR'] = $value->FUR;
        $result[$month]['LEAV'] = $value->LEAV;
        $result[$month]['TD'] = $value->TD;
        $result[$month]['NetPay'] = $value->NetPay;
        $result[$month]['TEarn'] = $value->TEarn;
        //var_dump($result);
    }
    $fullmonth=array("JANUARY","FEBRUARY","MARCH","APRIL","MAY","JUNE","JULY", "AUGUST","SEPTEMBER","OCTOBER","NOVEMBER", "DECEMBER" );
    $rowcount=0;
    $empty=0.0;
    for ($row = 0; $row <=11; $row++)
    {
        $currentmonth=$fullmonth[$row];
        if (!isset($result[$currentmonth]['Bs']))
        {
            $result[$currentmonth]['Bs'] = $empty;
            $result[$currentmonth]['TEarn'] = $empty;
            $result[$currentmonth]['TAX'] = $empty;
            $result[$currentmonth]['PEN'] = $empty;
            $result[$currentmonth]['NHF'] = $empty;
            $result[$currentmonth]['UD'] = $empty;
            $result[$currentmonth]['HA'] = $empty;
            $result[$currentmonth]['ML'] = $empty;
            $result[$currentmonth]['TR'] = $empty;
            $result[$currentmonth]['FUR'] = $empty;
            $result[$currentmonth]['LEAV'] = $empty;
            $result[$currentmonth]['TD'] = $empty;

            $result[$currentmonth]['NetPay'] = $empty;
            $result[$currentmonth]['TEarn'] = $empty;


        }
    }
    $imageNewName        = $fileno . '.jpg';
    $path                = base_path() . '/public/passport/';
    if(File::exists(base_path() . '/public/passport/' . $imageNewName )) //check folder
    {
        $user_picture = $imageNewName;
    }else{
        $user_picture =  '0.png';
    }
    $data['image'] = $user_picture;
    $data['result'] = $result;

        return view('/pecard/peReport',$data);
}
public function calculation($year, $fileno, $mth, Request $request)
{
	$mth = $mth;
	DB::enableQueryLog();
	$result= DB::table('tblper')
	->select('tblper.fileNo', 'name', 'nhf', 'unionDues', 'tblpayment.month', 'tblpayment.year', 'employee_type','tblbanklist.bank','tblper.bankGroup','tblarrears.*')
	->join('tblarrears', 'tblarrears.fileno', '=','tblper.fileno')
	->join('tblpayment', function($join) {
		$join->on('tblpayment.fileNo', '=', 'tblper.fileNo');
		$join->on('tblpayment.month', '=', 'tblarrears.month');
		$join->on('tblpayment.year', '=', 'tblarrears.year');
	})	
	->join('tblbanklist', 'tblbanklist.bankID', '=','tblper.bankID')
	->where('tblper.fileno', '=', $fileno)
	->where('tblpayment.year', '=', $year)
	->where('tblpayment.month', '=', $mth)
	->first();
	// $result = DB::select('select b.*, d.*, c.NumofPA from tblper a inner join (select * from basicsalary where (grade, step) IN ( (?, ?), (?, ?) ) ) b on (a.employee_type = b.employee_type) INNER join tblcv d on (a.fileNo = d.fileNo) and a.fileNo = ? LEFT join (SELECT fileNo, count(*) as NumofPA from tblpersonalassistant group by fileNo) c on (c.fileNo = a.fileNo)', [$myarray->grade, $myarray->step, $myarray->newGrade, $myarray->newStep, $myarray->staffList]);
	//dd(DB::getQueryLog());
	//dd($result);
	$data['query']=$result;
	$month = date_parse($mth);
	$month=$month['month'];
	$date2 ="$year-$month-01";
	$date1 =$result->dueDate;
	$diff =$this->dateDiff($date2, $date1);
	$data['month_diff']=$month_diff= $diff['months'];
// dd($date2);
	$data['day_diff']=$day_diff=$diff['days'];
	$data['daysOfMonth'] =$daysOfMonth= $diff['days_of_month'];
//calculate arrears duration
// 	$type =$result->type;
// //dd($type);
// 	if($type=="advancement")
// 	{
// 		$data['arrearsduration']=$month_diff." months ".$day_diff. " days out of ".$daysOfMonth." days"; 
// 	}elseif ($type=="appointment"){$data['arrearsduration']=$month_diff." months ".$day_diff. " days out of ".$daysOfMonth." days";  }
// 	elseif ($type=="newAppointment"){$data['arrearsduration']=$month_diff." months ".$day_diff. " days out of ".$daysOfMonth." days";  }
// 	else{$data['arrearsduration']=$month_diff." months "; }
	
// 	if($type!="new-appointment")
// 	{
// 		$newdetails= DB::table('basicsalary')
// 		->where('employee_type', '=',$result->employee_type)
// 		->where('grade', '=', $result->newGrade )
// 		->where('step', '=',$result->newStep )
// 		->first();
// 		$data['row2']=$newdetails;
// 	}
// 	$data['date1']=$date1;
// 	$data['mth']=$mth;
// 	$data['year']=$year;
// 	$data['query']=$result;
// 	$data['row1']=$olddetails;
// 	$data['row']=$result;

// 	if(($type=="new appointment") or($type=="appointment"))
// 	{
// 		return view('pecard.workings_app',$data);
// 	}
	return view('pecard.workings',$data);
}
public function new_app($year,$fileno,$mth,Request $request)
{
	$result = DB::table('tblper')
	->select('tblper.fileNo', 'surname','first_name','othernames','employee_type', 'tblbanklist.bank','tblpayment.bankGroup','appointment_date', 'tblarrears.*')
	->join('tblarrears', 'tblarrears.fileno', '=','tblper.fileno')
	->join('tblbanklist', 'tblbanklist.bankID', '=','tblper.bankID')
	->join('tblpayment', 'tblpayment.fileNo', '=', 'tblper.fileNo')
	->where('tblper.fileno', '=', $fileno)
	->where('tblarrears.type', '=', 'new-appointment')
	->where('tblpayment.year', '=', $year)
	->where('tblpayment.month', '=', $mth)
	->first();
	//$data['query']=$result;
	$month = date_parse($mth);
	$month = $month['month'];
	$date2 = "$year-$month-01";

	$date1 =$result->appointment_date;
	$diff =$this->dateDiff($date2, $date1);
	$data['month_diff']=$month_diff= $diff['months'];
	$data['day_diff']=$day_diff= $diff['days'];
	$data['daysOfMonth'] =$daysOfMonth=$diff['days_of_month'];
	$data['result'] = $result;
	$data['mth']=$mth;
	$data['year']=$year;
	
	return view('pecard.workings_app',$data);
}
function dateDiff($date2, $date1)
{
	list($year2, $mth2, $day2) = explode("-", $date2);
	list($year1, $mth1, $day1) = explode("-", $date1);
	if ($year1 > $year2) die('Invalid Input - dates do not match');
	$days_month = 0;
	$day_diff = 0;
	if($year2 == $year1)
		$mth_diff = $mth2 - $mth1;
	else{
		$yr_diff = $year2 - $year1;
		$mth_diff = (12 * $yr_diff) - $mth1 + $mth2;
	}
	if($day1 > 1){
		$mth_diff--;
		$days_month = cal_days_in_month(CAL_GREGORIAN, $mth1, $year1);
		$day_diff = $days_month - $day1 + 1;
	}
	$result = array('months'=>$mth_diff, 'days'=> $day_diff, 'days_of_month'=>$days_month);
	return($result);
}  
}
