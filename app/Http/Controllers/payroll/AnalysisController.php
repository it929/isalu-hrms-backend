<?php
namespace App\Http\Controllers\payroll;


use App\Http\Requests;
use App\Role;
use App\User;
use Entrust;
use App\Permission;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;
use App\Http\Controllers\payroll\ParentController;

class AnalysisController extends ParentController
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
    // $this->division = $request->session()->get('division');
    // $this->divisionID = $request->session()->get('divisionID');
            return $next($request);
        });
    }

public function create()
{
$data['CourtInfo']=$this->CourtInfo();
      if($data['CourtInfo']->courtstatus==0){$request['court']=$data['CourtInfo']->courtid;}
      if($data['CourtInfo']->divisionstatus==0){$request['division']=$data['CourtInfo']->divisionid;}
  $data['courts'] =  DB::table('tbl_court')->get();
      $courtSessionId = session('anycourt');
         $data['courtDivisions']  = DB::table('tbldivision')
         ->where('courtID', '=', $courtSessionId)
         ->get();

          $data['allbanklist']  = DB::table('tblbanklist')
         ->orderBy('tblbanklist.bank', 'Asc')
         ->get();

  return view('payroll.summary.index',$data);
}

public function Retrieve(Request $request)
{
    $month     = trim($request->input('month'));
    $year      = trim($request->input('year'));
    $bankID    = trim($request->input('bankName'));
    $bankGroup = trim($request->input('bankGroup'));
    //$warrant   = trim($request->input('warrant'));
    $division  = trim($request->input('division'));
     $court    = trim($request->input('court'));

    $this->validate($request,[
          'month'     => 'required|string',
          'year'      => 'required|integer',
          //'bankName'  => 'required|integer',
          //'bankGroup' => 'required|integer',
          //'warrant'   => 'required|regex:/[a-zA-Z.]/'
    ]);
    $getBank  = DB::table('tblbanklist')
              ->where('bankID', $bankID)
              ->first();
              if($bankID !='')
              {
    $bankName = $getBank -> bank;
    }
    /*$data['summary_detail'] = DB::table('tblpayment')
            ->where('tblpayment.month',     '=', $month)
            ->where('tblpayment.year',      '=', $year)
            ->where('tblpayment.division',  '=', $division)
            ->where('tblpayment.bank',      '=', $bankName )
            ->where('tblpayment.bankGroup', '=', $bankGroup)
            ->orderBy('basic_salary','DESC')
            ->get();*/



       if($bankID != '')
      {
      $data['courtname']  = DB::table('tbl_court')
      ->where('id','=', $court)
      ->first();
      $data['courtDivisions'] = '';
       $data['summary_detail'] = DB::table('tblpayment_consolidated')
          ->where('tblpayment_consolidated.month',     '=', $month)
          ->where('tblpayment_consolidated.year',      '=', $year)
          ->where('tblpayment_consolidated.courtID',  '=', $court)
          ->where('tblpayment_consolidated.bank',      '=',$bankID )
          //->where('tblpayment_consolidated.bankGroup', '=', $bankGroup)
          ->where('tblpayment_consolidated.rank','!=',2)
          //->orderBy('basic_salary','DESC')
          ->get();
          return view('summary.summary', $data);
      }
      elseif($division != '' &&  $bankGroup != '')
      {

       $data['courtname'] = '';
      $data['courtDivisions']  = DB::table('tbl_court')
      ->join('tbldivision','tbldivision.courtID','=', 'tbl_court.id')
      ->where('tbl_court.id','=',$court)
      ->where('tbldivision.divisionID','=',$division)
      ->first();
       $data['summary_detail'] = DB::table('tblpayment_consolidated')
          ->where('tblpayment_consolidated.month',     '=', $month)
          ->where('tblpayment_consolidated.year',      '=', $year)
          ->where('tblpayment_consolidated.divisionID',  '=', $division)
          ->where('tblpayment_consolidated.courtID',  '=', $court)
          ->where('tblpayment_consolidated.bank',      '=',$bankID )
          ->where('tblpayment_consolidated.bankGroup', '=', $bankGroup)
          ->where('tblpayment_consolidated.rank','!=',2)
          //->orderBy('basic_salary','DESC')
          ->get();
      }
      if($bankGroup == '' && $bankID=='')
      {
        if($division == '')
      {
      $data['courtname']  = DB::table('tbl_court')
      ->where('id','=', $court)
      ->first();
      $data['courtDivisions'] = '';
       $data['summary_detail'] = DB::table('tblpayment_consolidated')
          ->where('tblpayment_consolidated.month',     '=', $month)
          ->where('ttblpayment_consolidated.year',      '=', $year)
          ->where('tblpayment_consolidated.courtID',  '=', $court)
          ->where('tblpayment_consolidated.rank','!=',2)
          //->where('tblpayment_consolidated.bank',      '=',$bankID )
          //->where('tblpayment.bankGroup', '=', $bankGroup)
          //->orderBy('basic_salary','DESC')
          ->get();
      }
      else
      {

       $data['courtname'] = '';
      $data['courtDivisions']  = DB::table('tbl_court')
      ->join('tbldivision','tbldivision.courtID','=', 'tbl_court.id')
      ->where('tbl_court.id','=',$court)
      ->where('tbldivision.divisionID','=',$division)
      ->first();
       $data['summary_detail'] = DB::table('tblpayment_consolidated')
          ->where('tblpayment_consolidated.month',     '=', $month)
          ->where('tblpayment_consolidated.year',      '=', $year)
          ->where('tblpayment_consolidated.divisionID',  '=', $division)
          ->where('tblpayment_consolidated.courtID',  '=', $court)
          ->where('tblpayment_consolidated.rank','!=',2)
          //->where('tblpayment_consolidated.bank',      '=',$bankID )
          //->where('tblpayment.bankGroup', '=', $bankGroup)
          //->orderBy('basic_salary','DESC')
          ->get();
      }
      }

//dd($data['summary_detail']);

    Session::put('schmonth', $month." ".$year);
    if($bankID != '')
    {
    Session::put('bank', $bankName ." ".$bankGroup);
    }
    //Session::put('warrant', $warrant);
    return view('payroll.summary.summary', $data);
  }

  public function calculateSum($month,$year,$field)
  {
       $data['basic'] = DB::table('tblpayment_consolidated')
    ->join('tblbanklist','tblbanklist.bankID','=','tblpayment_consolidated.bank')
    ->where('tblpayment_consolidated.month',     '=', $month)
    ->where('tblpayment_consolidated.year',      '=', $year)
    ->where('tblpayment_consolidated.rank','!=',2)
    ->groupBy('tblpayment_consolidated.bank')
    ->sum($field);
  }
   public function countStaff($month,$year)
  {
       $data['basic'] = DB::table('tblpayment_consolidated')
    ->join('tblbanklist','tblbanklist.bankID','=','tblpayment_consolidated.bank')
    ->where('tblpayment_consolidated.month','=', $month)
    ->where('tblpayment_consolidated.year', '=', $year)
    ->where('tblpayment_consolidated.rank','!=',2)
    ->groupBy('tblpayment_consolidated.bank')
    ->count();
  }

  public function analysis()
  {
  $data['CourtInfo']=$this->CourtInfo();
      if($data['CourtInfo']->courtstatus==0){$request['court']=$data['CourtInfo']->courtid;}
      if($data['CourtInfo']->divisionstatus==0){$request['division']=$data['CourtInfo']->divisionid;}
  $data['courts'] =  DB::table('tbl_court')->get();
      $courtSessionId = session('anycourt');
         $data['courtDivisions']  = DB::table('tbldivision')
         ->where('courtID', '=', $courtSessionId)
         ->get();

         $data['courtDivisions']  = DB::table('tbldivision')
            //  ->where('courtID', '=', $courtSessionId)
                ->get();
        $data['curDivision'] = $this->curDivision(Auth::user()->id);

          $data['allbanklist']  = DB::table('tblbanklist')
         ->orderBy('tblbanklist.bank', 'Asc')
         ->get();

  return view('payroll.summary.analysisParam',$data);
  }

  public function curDivision($userId){
    $currentDivision = DB::table("users")
                            ->join('tbldivision', 'tbldivision.divisionID', '=', 'users.divisionID')
                            ->where('users.id', '=', $userId)
                            ->select('tbldivision.division', 'tbldivision.divisionID')
                            ->first();
   return $currentDivision;
}


  public function analysisDisplay(Request $request)
  {
      $month     = trim($request->input('month'));
      $year      = trim($request->input('year'));
      $divisionID      = trim($request->input('division'));
      $data['divisionName'] = DB::table('tbldivision')->where('divisionID', '=', $divisionID)->value('division');
      $staffEarnElement = [];
      $staffDeductionElement = [];
      $getStaffMonthEarnAmount = [];
      $getStaffMonthDeductionAmount = [];


      //Get all banks
      $allBanks  =  DB::table('tblpayment_consolidated')
                ->leftjoin('tblbanklist', 'tblbanklist.bankID', '=', 'tblpayment_consolidated.bank')
                ->where('tblpayment_consolidated.month',     '=', $month)
                ->where('tblpayment_consolidated.year',      '=', $year)
                ->where('tblpayment_consolidated.divisionID', $divisionID ? '=' : '<>', $divisionID)
                ->where('tblpayment_consolidated.rank', '<>', 2)
                ->groupBy('tblpayment_consolidated.bank')
                ->select(
                    'tblpayment_consolidated.bank', 'tblbanklist.bank as bank_name',
                    DB::Raw("COUNT(staffid) as totalStaffNo"),
                    DB::raw("SUM(BS) as totalBS"),
                    DB::raw("SUM(TEarn) as totalTEarn"),
                    DB::raw("SUM(OEarn) as totalOEarn"),
                    DB::raw("SUM(NetPay) as totalNetPay"),
                    DB::raw("SUM(TD) as totalTD"),
                    DB::raw("SUM(PEC) as totalPEC"),
                    DB::raw("SUM(PECFG) as totalPECFG"),
                    DB::raw("SUM(UD) as totalUD"),
                    DB::raw("SUM(PEN) as totalPEN"),
                    DB::raw("SUM(TAX) as totalTAX"),
                    DB::raw("SUM(NHF) as totalNHF"),
                    DB::raw("SUM(OD) as totalOD"),
                    DB::raw("SUM(AEarn) as totalAEarn")
                  )
                ->get();

       //Dynamic Elements
       $getdynamicData = $this->DynamicEaringDeduction($year, $month, $divisionID, $allBanks);
       $staffEarnElement = $getdynamicData['staffEarnElement'];
       $staffDeductionElement = $getdynamicData['staffDeductionElement'];
       $getStaffMonthEarnAmount = $getdynamicData['getStaffMonthEarnAmount'];
       $getStaffMonthDeductionAmount = $getdynamicData['getStaffMonthDeductionAmount'];

      //data
      $data['allBanks']         =  $allBanks;
      $data['staffEarnElement'] = $staffEarnElement;
      $data['staffDeductionElement'] = $staffDeductionElement;
      $data['getStaffMonthEarnAmount'] = $getStaffMonthEarnAmount;
      $data['getStaffMonthDeductionAmount'] = $getStaffMonthDeductionAmount;

      $data['month'] = trim($request->input('month'));
      $data['year'] = trim($request->input('year'));
    return view('payroll.summary.analysis',$data);
  }

  public function summaryAnalysis()
  {
      $data['CourtInfo'] = $this->CourtInfo();
      if ($data['CourtInfo']->courtstatus == 0) {
          $request['court'] = $data['CourtInfo']->courtid;
      }
      if ($data['CourtInfo']->divisionstatus == 0) {
          $request['division'] = $data['CourtInfo']->divisionid;
      }
      $data['courts'] =  DB::table('tbl_court')->get();
      $courtSessionId = session('anycourt');
      $data['courtDivisions']  = DB::table('tbldivision')
          ->where('courtID', '=', $courtSessionId)
          ->get();

      $data['courtDivisions']  = DB::table('tbldivision')
          //  ->where('courtID', '=', $courtSessionId)
          ->get();
      $data['curDivision'] = $this->curDivision(Auth::user()->id);

      $data['allbanklist']  = DB::table('tblbanklist')
          ->orderBy('tblbanklist.bank', 'Asc')
          ->get();

      return view('payroll.summary.summaryanalysisParam', $data);
  }

  public function summaryAnalysisDisplay(Request $request)
  {
      $month              = trim($request->input('month'));
      $year               = trim($request->input('year'));
      $divisionID         = trim($request->input('division'));
      $staffInBank        = [];
      $staffName          = [];
      $staffListBank      = [];
      $staffListName      = [];
      $variableElement    = [];

      //Get division data
      $divisionData = DB::table('tblpayment_consolidated')
          ->leftjoin('tbldivision', 'tbldivision.divisionID', '=', 'tblpayment_consolidated.divisionID')
          ->where('tblpayment_consolidated.divisionID', $divisionID)
          ->first();

      // Extract just the division name or set to null
      $data['mydivision'] = $divisionData ? ($divisionData->division ?? null) : null;

      //Get all banks
      $allBanks  =  DB::table('tblpayment_consolidated')
          ->join('tblbanklist', 'tblbanklist.bankID', '=', 'tblpayment_consolidated.bank')
          ->where('tblpayment_consolidated.month',     '=', $month)
          ->where('tblpayment_consolidated.year',      '=', $year)
          ->where('tblpayment_consolidated.divisionID', $divisionID ? '=' : '<>', $divisionID)
          ->where('tblpayment_consolidated.rank', '<>', 2)
          ->groupBy('tblpayment_consolidated.bank')
          ->select('tblpayment_consolidated.bank', 'tblbanklist.bank as bank_name')
          ->get();

      //compute data
      foreach ($allBanks as $key => $bankVal) {
          //Get staff for banks
          $staffListBank[$key] = DB::table('tblpayment_consolidated')
              ->where('tblpayment_consolidated.month',     '=', $month)
              ->where('tblpayment_consolidated.year',      '=', $year)
              ->where('tblpayment_consolidated.bank', $bankVal->bank)
              ->where('tblpayment_consolidated.rank', '<>', 2)
              ->where('tblpayment_consolidated.divisionID', $divisionID ? '=' : '<>', $divisionID)
              ->select('staffid')
              ->get();

          $staffInBank[$bankVal->bank] = count($staffListBank[$key]);

          $staffListName[$key] = DB::table('tblpayment_consolidated')
              ->where('tblpayment_consolidated.month',     '=', $month)
              ->where('tblpayment_consolidated.year',      '=', $year)
              ->where('tblpayment_consolidated.bank', $bankVal->bank)
              ->where('tblpayment_consolidated.rank', '<>', 2)
              ->where('tblpayment_consolidated.divisionID', $divisionID ? '=' : '<>', $divisionID)
              ->select('name')
              ->first();
          $staffName[$bankVal->bank] = $staffListName[$key];

          //get variable sum
          $variableElement[$bankVal->bank] = DB::table('tblpayment_consolidated')
              ->join('tblbanklist', 'tblbanklist.bankID', '=', 'tblpayment_consolidated.bank')
              ->where('tblpayment_consolidated.month',     '=', $month)
              ->where('tblpayment_consolidated.year',      '=', $year)
              ->where('tblpayment_consolidated.bank', $bankVal->bank)
              ->where('tblpayment_consolidated.divisionID', $divisionID ? '=' : '<>', $divisionID)
              ->where('tblpayment_consolidated.rank', '<>', 2)
              ->groupBy('tblpayment_consolidated.bank')
              ->select(
                  '*',
                  DB::raw("SUM(BS) as totalBS"),
                  DB::raw("SUM(NetPay) as totalNetPay"),
                  DB::raw("SUM(TD) as totalTD"),
                  DB::raw("SUM(PEC) as totalPEC"),
                  DB::raw("SUM(UD) as totalUD"),
                  DB::raw("SUM(PEN) as totalPEN"),
                  DB::raw("SUM(TAX) as totalTAX"),
                  DB::raw("SUM(OEarn) as totalOEarn"),
                  DB::raw("SUM(TEarn) as totalTEarn"),
                  DB::raw("SUM(NHF) as totalNHF"),
                  DB::raw("SUM(OD) as totalOD")
              )
              ->first();
      }

      //data
      $data['variableElement']  = $variableElement;
      $data['staffInBank']      = $staffInBank;
      $data['staffName']        = $staffName;
      $data['allBanks']         = $allBanks;
      $data['month'] = trim($request->input('month'));
      $data['year'] = trim($request->input('year'));

      return view('payroll.summary.summaryanalysis', $data);
  }

  public function summaryBankMandateAnalysis()
  {
      $data['CourtInfo'] = $this->CourtInfo();
      if ($data['CourtInfo']->courtstatus == 0) {
          $request['court'] = $data['CourtInfo']->courtid;
      }
      if ($data['CourtInfo']->divisionstatus == 0) {
          $request['division'] = $data['CourtInfo']->divisionid;
      }
      $data['courts'] =  DB::table('tbl_court')->get();
      $courtSessionId = session('anycourt');
      $data['courtDivisions']  = DB::table('tbldivision')
          ->where('courtID', '=', $courtSessionId)
          ->get();

      $data['courtDivisions']  = DB::table('tbldivision')
          ->get();
      $data['curDivision'] = $this->curDivision(Auth::user()->id);

      $data['allbanklist']  = DB::table('tblbanklist')
          ->orderBy('tblbanklist.bank', 'Asc')
          ->get();

      return view('payroll.summary.summarybankMandateAnalysisParam', $data);
  }

  public function summaryBankMandateAnalysisDisplay(Request $request)
  {
      $month              = trim($request->input('month'));
      $year               = trim($request->input('year'));
      $divisionID         = trim($request->input('division'));
      $data['bank']             = trim($request->input('bank'));

      $data['accountDetails'] = DB::table('tblmandate_address_account')
            ->join('tblbanklist', 'tblbanklist.bankID', '=', 'tblmandate_address_account.bankId')
            ->join('tblcontractType', 'tblcontractType.ID', '=', 'tblmandate_address_account.contractTypeID')
            ->select(
                'tblmandate_address_account.*', 
                'tblbanklist.bank',
                'tblcontractType.contractType'
            )
            ->where('tblmandate_address_account.contractTypeID', 6)
            ->where('tblmandate_address_account.status', 1)
            ->first(); // Use first() since you want only one record
        $data['monthNumber'] = date('n', strtotime($month));

      if($data['bank'] == 'CBN'){
        $data['reportData'] = DB::table('tblpayment_consolidated')
        ->where('tblpayment_consolidated.year', $year)
        ->where('tblpayment_consolidated.month', $month)
        ->select(  
            'tblpayment_consolidated.vstage',
            DB::raw('SUM(NHF) as total_nhf'),
            DB::raw('SUM(NSITF) as total_nsitf'),
            DB::raw('SUM(CASE WHEN current_state = 37 THEN TAX ELSE 0 END) as total_tax'),
            DB::raw('
                SUM(NHF)
              + SUM(NSITF)
              + SUM(CASE WHEN current_state = 37 THEN TAX ELSE 0 END)
              as grand_total
            ')
        )
        ->first();
      }

      if($data['bank'] == 'NASARAWA'){
        $data['reportData'] = DB::table('tblpayment_consolidated')
        ->where('tblpayment_consolidated.year', $year)
        ->where('tblpayment_consolidated.month', $month)
        ->select(
            'tblpayment_consolidated.vstage',
            DB::raw('SUM(CASE WHEN current_state = 30 THEN TAX ELSE 0 END) as total_tax'),
            DB::raw('
              SUM(CASE WHEN current_state = 30 THEN TAX ELSE 0 END)
              as grand_total
            ')
        )
        ->first();
      }

      if($data['bank'] == 'NIGER'){
        $data['reportData'] = DB::table('tblpayment_consolidated')
        ->where('tblpayment_consolidated.year', $year)
        ->where('tblpayment_consolidated.month', $month)
        ->select(
            'tblpayment_consolidated.vstage',
            DB::raw('SUM(CASE WHEN current_state = 25 THEN TAX ELSE 0 END) as total_tax'),
            DB::raw('
               SUM(CASE WHEN current_state = 25 THEN TAX ELSE 0 END)
              as grand_total
            ')
        )
        ->first();
      }





        if ($data['bank'] == 'COMMERCIAL') {

            $data['commercialRecords'] = DB::table('tblpayment_consolidated')
                ->join('tblper', 'tblper.ID', '=', 'tblpayment_consolidated.staffid')
                ->join('tblbanklist', 'tblbanklist.bankID', '=', 'tblpayment_consolidated.bank')
                ->where('tblpayment_consolidated.year', $year)
                ->where('tblpayment_consolidated.month', $month)
                ->whereNotIn('tblpayment_consolidated.bank', [37, 39])
                ->select(
                    'tblpayment_consolidated.vstage',
                    'tblper.surname',
                    'tblper.first_name',
                    'tblper.othernames',
                    'tblbanklist.bank',
                    'tblpayment_consolidated.NetPay'
                )
                ->get();

            $data['commercialTotal'] = DB::table('tblpayment_consolidated')
                ->where('year', $year)
                ->where('month', $month)
                ->whereNotIn('bank', [37, 39])
                ->sum('NetPay');
        }


        if ($data['bank'] == 'Micro_Finance') {

            $data['microfinanceRecords'] = DB::table('tblpayment_consolidated')
                ->join('tblper', 'tblper.ID', '=', 'tblpayment_consolidated.staffid')
                ->join('tblbanklist', 'tblbanklist.bankID', '=', 'tblpayment_consolidated.bank')
                ->where('tblpayment_consolidated.year', $year)
                ->where('tblpayment_consolidated.month', $month)
                ->whereIn('tblpayment_consolidated.bank', [37, 39])
                ->select(
                    'tblpayment_consolidated.vstage',
                    'tblper.surname',
                    'tblper.first_name',
                    'tblper.othernames',
                    'tblbanklist.bank',
                    'tblpayment_consolidated.NetPay'
                )
                ->get();

            $data['microfinanceTotal'] = DB::table('tblpayment_consolidated')
                ->where('year', $year)
                ->where('month', $month)
                ->whereIn('bank', [37, 39])
                ->sum('NetPay');
        }

        if ($data['bank'] == 'UNION_DUES') {
            $data['reportData'] = DB::table('tblpayment_consolidated')
            ->where('tblpayment_consolidated.year', $year)
            ->where('tblpayment_consolidated.month', $month)
            ->select(  
                'tblpayment_consolidated.vstage',
                DB::raw('SUM(UD) as UD')
            )
            ->first();
        }


      return view('payroll.summary.summaryBankMandateAnalysis', $data);

     }

  public function DynamicEaringDeduction($year = null, $month = null, $division = null, $allBanks = [])
  {
    //=========================START===================================
    $getStaffMonthEarn = [];
    $getStaffMonthDeduction = [];


    //Get list of Earning for all staff
    $data['staffEarnElement'] = DB::table('tblotherEarningDeduction')
      ->Join('tblcvSetup', 'tblotherEarningDeduction.CVID', '=', 'tblcvSetup.ID')
      ->whereNotBetween('tblcvSetup.ID', [11, 23])
      ->where('tblotherEarningDeduction.year', $year)
      ->where('tblotherEarningDeduction.month', $month)
      ->where('tblotherEarningDeduction.divisionID', $division ? '=' : '<>', $division)
      ->where('tblotherEarningDeduction.particularID', 1)
      ->where('tblotherEarningDeduction.amount', '<>', 0)
      ->orderBy('tblcvSetup.rank')
      ->groupBy('tblotherEarningDeduction.CVID')
      ->select('description', 'divisionID', 'amount', 'tblotherEarningDeduction.CVID', 'tblcvSetup.ID as cvsetupID', 'tblotherEarningDeduction.ID', 'rank', 'tblcvSetup.particularID', 'tblotherEarningDeduction.staffid')
      ->get();

    //Get list of Deduction for all staff
    $data['staffDeductionElement'] = DB::table('tblotherEarningDeduction')
      ->Join('tblcvSetup', 'tblotherEarningDeduction.CVID', '=', 'tblcvSetup.ID')
      ->where('tblotherEarningDeduction.year', $year)
      ->where('tblotherEarningDeduction.month', $month)
      ->where('tblotherEarningDeduction.divisionID', $division ? '=' : '<>', $division)
      ->where('tblotherEarningDeduction.particularID', 2)
      ->where('tblotherEarningDeduction.amount', '<>', 0)
      ->orderBy('tblcvSetup.rank')
      ->groupBy('tblotherEarningDeduction.CVID')
      ->select('description', 'amount', 'tblotherEarningDeduction.CVID', 'tblcvSetup.ID as cvsetupID', 'tblotherEarningDeduction.ID', 'rank', 'tblcvSetup.particularID', 'tblotherEarningDeduction.staffid')
      ->get();

    foreach ($allBanks as $bank)
    {
      $arrStaff = [];
      $staffListBank = DB::table('tblpayment_consolidated')
                      ->where('tblpayment_consolidated.month', '=', $month)
                      ->where('tblpayment_consolidated.year', '=', $year)
                      ->where('tblpayment_consolidated.bank', $bank->bank)
                      ->where('tblpayment_consolidated.rank', '<>', 2)
                      ->where('tblpayment_consolidated.divisionID', $division ? '=' : '<>', $division)
                      ->select('staffid')
                      ->get();

      foreach ($staffListBank as $value) {
        $arrStaff[] = $value->staffid;
      }

      ///Earning - Get Element amount for each and sum up duplicate element amount
      foreach ($data['staffEarnElement'] as $key2 => $staffCVEarn)
      {
        $getStaffMonthEarn[$bank->bank][$staffCVEarn->CVID] = DB::table('tblotherEarningDeduction')
              ->whereIn('tblotherEarningDeduction.staffid', $arrStaff)
              ->where('tblotherEarningDeduction.CVID', $staffCVEarn->CVID)
              ->where('tblotherEarningDeduction.year', $year)
              ->where('tblotherEarningDeduction.month', $month)
              ->where('tblotherEarningDeduction.divisionID', $division ? '=' : '<>', $division)
              ->first([DB::raw("SUM(tblotherEarningDeduction.amount) as staffEarnings")]);
      }

      //Deduction - Get Element amount for each and sum up duplicate element amount
      foreach ($data['staffDeductionElement'] as $key2 => $staffCVEarn) {
        $getStaffMonthDeduction[$bank->bank][$staffCVEarn->CVID] = DB::table('tblotherEarningDeduction')
              ->whereIn('tblotherEarningDeduction.staffid', $arrStaff)
              ->where('tblotherEarningDeduction.CVID', $staffCVEarn->CVID)
              ->where('tblotherEarningDeduction.year', $year)
              ->where('tblotherEarningDeduction.month', $month)
              ->where('tblotherEarningDeduction.divisionID', $division ? '=' : '<>', $division)
              ->first([DB::raw("SUM(tblotherEarningDeduction.amount) as staffDeductions")]);
      }
    }

    $data['getStaffMonthEarnAmount']        = $getStaffMonthEarn;
    $data['getStaffMonthDeductionAmount']   = $getStaffMonthDeduction;

    //=========================END=====================================
    return $data;
  }




   public function summaryByBank()
  {
  $data['CourtInfo']=$this->CourtInfo();
      if($data['CourtInfo']->courtstatus==0){$request['court']=$data['CourtInfo']->courtid;}
      if($data['CourtInfo']->divisionstatus==0){$request['division']=$data['CourtInfo']->divisionid;}
  $data['courts'] =  DB::table('tbl_court')->get();
      $courtSessionId = session('anycourt');
         $data['courtDivisions']  = DB::table('tbldivision')
         ->where('courtID', '=', $courtSessionId)
         ->get();

          $data['allbanklist']  = DB::table('tblbanklist')
         ->orderBy('tblbanklist.bank', 'Asc')
         ->get();

  return view('payroll.summary.bybanks',$data);
  }

  public function summaryPostBank(Request $request)
  {
    $month     = trim($request->input('month'));
    $year      = trim($request->input('year'));

    $data['month'] = $month;
    $data['year'] = $year;

  /*$data['group'] = DB::table('tblpayment_consolidated')
    ->join('tblbanklist','tblbanklist.bankID','=','tblpayment_consolidated.bank')
    ->where('tblpayment_consolidated.month',     '=', $month)
    ->where('tblpayment_consolidated.year',      '=', $year)
    ->orderBy('tblpayment_consolidated.bank', 'Asc')
    ->select('*','tblbanklist.bank as staffbank','tblpayment_consolidated.bank as bk')

    ->get();*/
    $data['epayment_detail'] = DB::table('tblpayment_consolidated')
       ->join('tblbanklist','tblbanklist.bankID','=','tblpayment_consolidated.bank')
        ->leftJoin('tblbacklog','tblbacklog.staffid','=','tblpayment_consolidated.staffid','tblbacklog.month','=','tblpayment_consolidated.month','tblbacklog.year','=','tblpayment_consolidated.year')
        //->where('tblbacklog.month',     '=', $month)
        //->where('tblbacklog.year',      '=', $year)
        ->where('tblpayment_consolidated.month',     '=', $month)
        ->where('tblpayment_consolidated.year',      '=', $year)
         ->where('tblpayment_consolidated.rank','!=',2)
        //->where('tblpayment_consolidated.divisionID',  '=', $division)
        //->where('tblpayment_consolidated.courtID',  '=', $court)
        ->orderBy('tblpayment_consolidated.bank','DESC')
        ->orderBy('tblpayment_consolidated.rank','DESC')
        ->orderBy('tblpayment_consolidated.name','ASC')
        ->get();
        //dd($data['epayment_detail']);
        $data['epayment_total'] = DB::table('tblpayment_consolidated')

        ->where('tblpayment_consolidated.month',     '=', $month)
        ->where('tblpayment_consolidated.year',      '=', $year)
        //->where('tblpayment_consolidated.divisionID',  '=', $division)
        //->where('tblpayment_consolidated.courtID',  '=', $court)
        //->where('tblpayment_consolidated.bank',      '=', $bankID )
       // ->where('tblpayment_consolidated.bankGroup', '=',$bankGroup)
       ->where('tblpayment_consolidated.rank','!=',2)
       ->orderBy('tblpayment_consolidated.grade','DESC')
        //->orderBy('tblpayment_consolidated.step','DESC')
        ->orderBy('tblpayment_consolidated.bank','DESC')
        ->orderBy('tblpayment_consolidated.rank','DESC')
        ->orderBy('tblpayment_consolidated.name','ASC')

        ->get();

    return view('payroll.summary.summaryByBanks2',$data);
  }
  Public function ContravariableSum($year,$month,$cvid,$bank){
	$List= DB::Select("SELECT sum(`amount`) as sumtotal FROM `tblotherEarningDeduction` WHERE `CVID`='$cvid' and `tblotherEarningDeduction`.`month`='$month' and `tblotherEarningDeduction`.`year`='$year' and
	exists( SELECT * FROM `tblpayment_consolidated` WHERE `tblpayment_consolidated`.`staffid`=`tblotherEarningDeduction`.`staffid` and `tblpayment_consolidated`.`year`='$year'
	and `tblpayment_consolidated`.`month`='$month' and `tblpayment_consolidated`.`bank`='$bank' and tblpayment_consolidated.rank !=2)");
	if ($List){return $List[0]->sumtotal;} else {return 0;}
	}



}
