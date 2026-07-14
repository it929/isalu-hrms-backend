<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
  <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
  <link rel="shortcut icon" href="images/favicon.ico">
  <title>ISALU HOSPITAL LIMITED PAYROLL
    ...::...Payroll Report</title>

<link rel="stylesheet" href="{{ asset('assets/css/bootstrap.min.css') }}">
<style type="text/css">
.head-color tr td, .table .th-row td
{
color:#06c;

}
.table, .table tr td
{
border: 1px solid #06C;
color:#06c;
}
.pr
        {
         display:none;
        }
</style>
 <style media="print">
        .pr
        {
         display:block;
        }
    </style>
  <script type="text/javascript" src="{{ asset('assets/js/number_to_word.js') }}">
  </script>
</head>


<body>
<div align="center"><h2><div style="color:#06c;">ISALU HOSPITAL LIMITED
      <br />
      PAYROLL</div><br />
  </h2>
</div>

<div style="width:90%; margin: auto;">
<table class="head-color" width="1802" border="0" cellpadding="0" cellspacing="0" style="font-size:18px" >
  <tr>
    <td>Payroll P.V. No:</td>
    <td><div align="left">Sheet No:</div></td>
  </tr>
  <tr>
    <td colspan="2"><h3>MINISTRY/DEPARTMENT:
        ,
        @if($courtname != ''){{ $courtname->court_name }} @elseif($courtDivisions != '') {{$courtDivisions->court_name}}, ABUJA @endif</h3></td>
  </tr>
  <tr>
    <td width="1294">
      <strong>MONTH ENDING:  @if(session('schmonth'))
          {{ session('schmonth') }}

        @endif</strong><br/>       </td>
    <td width="508" align="rights">Date Printed: {{ date("l, F d, Y") }}</td>
  </tr>
  <tr>
    <td><strong>

        @if(session('bank'))
          {{ session('bank') }}

        @endif

      </strong> </td>
    <td>&nbsp;</td>
  </tr>
</table>


<table class="table table-condense table-responsive" border="1" cellpadding="4" cellspacing="0">
  <tr class="th-row">
   <td width="44" rowspan="2" align="center" valign="middle"><strong>SN</strong></td>
    <td width="44" rowspan="2" align="center" valign="middle"><strong>FILE NO</strong></td>
    <td width="271" rowspan="2" align="center" valign="middle"><strong>NAME</strong></td>
    <td width="23" rowspan="2" align="center" valign="middle"><strong>GL</strong></td>
    <td width="23" rowspan="2" align="center" valign="middle"><strong>ST</strong></td>
    @if($count_sot > 0)
   <td colspan="10" align="center" valign="top"><strong>EARNINGS</strong></td>
    @else
    <td colspan="9" align="center" valign="top"><strong>EARNINGS</strong></td>
    @endif

    <td colspan="6" align="center" valign="top"><strong>DEDUCTIONS</strong></td>

    <td width="70" rowspan="2" align="center" valign="middle"><strong>TOTAL DEDUCTION.</strong></td>


    <td width="75" rowspan="2" align="center" valign ="middle"><strong> TOTAL NET <BR/> EMOLUMENT </strong></td>
  </tr>
  <tr>

  <!-- Earning Starts -->
    <td width="90" align="center" valign="middle"><strong>BASIC <br/> (CONSOLIDATED)</strong></td>
    <td width="38" align="center" valign="middle"><strong>TOTAL <BR/>ARREARS <BR/> EARNING</strong></td>
    <td width="38" align="center" valign="middle"><strong>JUSU</strong></td>
    <td width="38" align="center" valign="middle"><strong>ACT ALL.</strong></td>
    <td width="38" align="center" valign="middle"><strong>SECRETARIAL ALL.</strong></td>
    <td width="52" align="center" valign="middle"><strong>OVERTIME</strong></td>
    <td width="38" align="center" valign="middle"><strong>HAZARD</strong></td>
    <td width="38" align="center" valign="middle"><strong>CALL DUTY</strong></td>
    @if($count_sot > 0)
    <td width="38" align="center" valign="middle"><strong>QUARTERLY <br/> OVERTIME</strong></td>
    @endif
    <td width="80" align="center" valign="middle"><strong>GROSS<br/> EMOLUMENT</strong></td>
  <!-- Earning Ends -->


    <td width="74" align="center" valign="middle"><strong>TAX</strong></td>
     <td width="85" align="center" valign="middle"><strong>NHF</strong></td>
      <td width="50" align="center" valign="middle"><strong>UNION DUES</strong></td>
    <td width="50" align="center" valign="middle"><strong>PENSION</strong></td>

    <td width="80" align="center" valign="middle"><strong>COOP</strong></td>
    <td width="85" align="center" valign="middle"><strong>SALARY ADVANCE</td>



  </tr>

  @php
    $bstotal = 0.00;
    $hatotal = 0.00;
    $trtotal = 0;
    $furtotal = 0;
    $taxtotal = 0;
    $pectotal = 0;
    $utitotal = 0;
    $drtotal = 0;
    $sertotal = 0;
    $e_arrearstotal = 0;
    $e_otherstotal = 0;
    $pentotal = 0;
    $nhftotal = 0;
    $d_arrearstotal = 0;
    $d_othertotal = 0;
    $earntotal = 0;
    $deducttotal = 0;
    $netpaytotal = 0;
    $uniontotal = 0;
    $totalNetEmolu = 0;
    $actAlltotal = 0;
    $secAlltotal = 0;
    $overtimetotal = 0;
    $calldutytotal = 0;
    $hazardtotal = 0;
    $cooptotal = 0;
    $salAdvancetotal = 0;
    $coopLoantotal =0;
    $coopSavingtotal =0;
    $totalSot = 0;

    $k = 1;
  @endphp


  @foreach ($payroll_detail as $reports)
    @php

     $coopSaving = DB::table('tblotherEarningDeduction')->where('staffid','=',$reports->staffid)->where('month','=',$month)->where('year','=',$year)->where('CVID','=',15)->first();

      $coopLoan = DB::table('tblotherEarningDeduction')->where('staffid','=',$reports->staffid)->where('month','=',$month)->where('year','=',$year)->where('CVID','=',16)->first();
        $salAdvance = DB::table('tblotherEarningDeduction')->where('staffid','=',$reports->staffid)->where('month','=',$month)->where('year','=',$year)->where('CVID','=',18)->first();
        $overtime = DB::table('tblotherEarningDeduction')->where('staffid','=',$reports->staffid)->where('month','=',$month)->where('year','=',$year)->where('CVID','=',13)->first();

       $actAll = DB::table('tblotherEarningDeduction')->where('staffid','=',$reports->staffid)->where('month','=',$month)->where('year','=',$year)->where('CVID','=',3)->first();
        $secAll = DB::table('tblotherEarningDeduction')->where('staffid','=',$reports->staffid)->where('month','=',$month)->where('year','=',$year)->where('CVID','=',17)->first();
        $hazard = DB::table('tblotherEarningDeduction')->where('staffid','=',$reports->staffid)->where('month','=',$month)->where('year','=',$year)->where('CVID','=',4)->first();
        $callduty = DB::table('tblotherEarningDeduction')->where('staffid','=',$reports->staffid)->where('month','=',$month)->where('year','=',$year)->where('CVID','=',22)->first();

        if(count($coopSaving) != 0)
        {
        $savings = $coopSaving->amount;
        }
        else
        {
         $savings = 0;
        }


        if(count($coopLoan) != 0)
        {
        $loan = $coopLoan->amount;
        }
        else
        {
         $loan = 0;
        }

      $fileNo = str_replace("/", "-", $reports->fileNo);
    @endphp
    <tr>
    <td align="right" >{{ $k++ }}</td>
      <td align="right" >{{ $reports->fileNo }}</td>
      <td align="left" valign="middle" nowrap="nowrap"><a class="hidden-print" target ="_blank" href="{{url("/con-pecard/getCard/$reports->staffid/$reports->year")}}">{{ $reports->name }}</a> <span class="pr">{{ $reports->name }}</span></td>
      <td width="23" align="center" valign="middle"> {{ $reports->grade }}</td>
      <td width="23" align="center" valign="middle">{{$reports->step}}</td>

      {{-- Earnings --}}
      <td width="75" align="right"><?php $bstotal += $reports->Bs; ?> {{number_format($reports->Bs, 2, '.', ',')}}</td>

      @if($reports->AEarn == "")
        <td width="66" align="right"><?php $e_arrearstotal += $reports->AEarn;?>{{number_format($reports->AEarn, 2, '.', ',')}}</td>
      @else
        <td width="66" align="right"><?php $e_arrearstotal += $reports->AEarn;?><a class="hidden-print" href="{{url("/con-payrollReport/arrears/$reports->courtID/$reports->staffid/$reports->year/$reports->month")}}" target="_blank">
 {{number_format($reports->AEarn, 2, '.', ',')}}</a><span class="pr">{{number_format($reports->AEarn, 2, '.', ',')}}</span></td>
      @endif

       <td width="75" align="right"><?php $pectotal += $reports->PEC; ?>{{number_format($reports->PEC, 2, '.', ',')}}</td>
        <td width="75" align="right"><?php $actAlltotal += (count($actAll) ==0) ? 0 : $actAll->amount; ?>@if(count($actAll) != 0) {{number_format($actAll->amount, 2, '.', ',')}} @endif</td>
        <td width="75" align="right"><?php $secAlltotal += (count($secAll) ==0) ? 0 : $secAll->amount; ?>@if(count($secAll) != 0) {{number_format($secAll->amount, 2, '.', ',')}} @endif </td>
         <td width="75" align="right"><?php $overtimetotal += (count($overtime) ==0) ? 0 : $overtime->amount; ?>@if(count($overtime) != 0) {{number_format($overtime->amount, 2, '.', ',')}} @endif </td>
         <td width="75" align="right"><?php $hazardtotal += (count($hazard) ==0) ? 0 : $hazard->amount; ?>@if(count($hazard) != 0) {{number_format($hazard->amount, 2, '.', ',')}} @endif</td>
         <td width="75" align="right"><?php $calldutytotal += (count($callduty)==0) ? 0 : $callduty->amount; ?>@if(count($callduty) != 0) {{number_format($callduty->amount, 2, '.', ',')}} @endif </td>
     @if($count_sot > 0)

      <td width="38" align="right"><?php $totalSot += $reports->SOT;?> {{number_format($reports->SOT, 2, '.', ',')}}</td>

      @endif

      <td width="80" align="right" style="background:#0cf; opacity:0.8"><?php $earntotal += ($reports->Bs + $reports->AEarn + $reports->OEarn + $reports->PEC + $reports->SOT);?> <strong>{{number_format($reports->Bs + $reports->AEarn + $reports->OEarn + $reports->PEC + $reports->SOT, 2, '.', ',')}} </strong></td>
     {{-- //Earnings --}}


      <td width="52" align="right"><?php $taxtotal += $reports->TAX;?> {{number_format($reports->TAX, 2, '.', ',')}}</td>
       <td width="50" align="right"><?php $nhftotal += $reports->NHF;?> {{number_format($reports->NHF, 2, '.', ',')}}</td>
       <td width="74" align="right"><?php $uniontotal += $reports->UD;?> {{number_format($reports->UD, 2, '.', ',')}}</td>
      <td width="74" align="right"><?php $pentotal += $reports->PEN;?> {{number_format($reports->PEN, 2, '.', ',')}}</td>

     <td width="85" align="right"> <?php $coopSavingtotal += (count($coopSaving) ==0 ) ? 0 : ($coopSaving->amount);?> <?php $coopLoantotal += (count($coopLoan) ==0 ) ? 0 : ($coopLoan->amount);?>   {{number_format($savings + $loan, 2, '.', ',')}}</td>
      <td width="85" align="right"><?php $salAdvancetotal += (count($salAdvance) ==0) ? 0 : $salAdvance->amount;?>@if(count($salAdvance) != 0) {{number_format($salAdvance->amount, 2, '.', ',')}} @endif </td>



      <td width="82" align="right" style="background:#0cf;opacity:0.8;"><?php $deducttotal += $reports->TD;?> <strong> {{number_format($reports->TD, 2, '.', ',')}}</strong></td>

      <td width="82" align="right"><?php $totalNetEmolu += $reports->NetPay;?> {{number_format($reports->NetPay, 2, '.', ',')}}</td>

    </tr>

  @endforeach



  <tr><td colspan="5" align="right"><strong>TOTAL</strong></td>

    <td align="right"><strong>{{ number_format($bstotal, 2, '.', ',') }}</strong></td>

        <td align="right"><strong>{{ number_format($e_arrearstotal, 2, '.', ',') }} </strong></td>

        <td align="right"><strong>{{ number_format($pectotal, 2, '.', ',') }}</strong></td>
    <td align="right"><strong>{{ number_format($actAlltotal, 2, '.', ',') }}</strong></td>
    <td align="right"><strong>{{ number_format($secAlltotal, 2, '.', ',') }}</strong></td>
    <td align="right"><strong>{{ number_format($overtimetotal, 2, '.', ',') }}</strong></td>
    <td align="right"><strong>{{ number_format($hazardtotal, 2, '.', ',') }}</strong></td>
    <td align="right"><strong>{{ number_format($calldutytotal, 2, '.', ',') }}</strong></td>
    <td align="right"><strong>{{ number_format($totalSot, 2, '.', ',') }}</strong></td>


    <td align="right"><strong>{{ number_format($earntotal, 2, '.', ',') }}</strong></td>

    <td align="right"><strong>{{ number_format($taxtotal, 2, '.', ',') }}</strong></td>
    <td align="right"><strong>{{ number_format($nhftotal, 2, '.', ',') }}</strong></td>
    <td align="right"><strong>{{ number_format($uniontotal, 2, '.', ',') }} </strong></td>
    <td align="right"><strong>{{ number_format($pentotal, 2, '.', ',') }} </strong></td>


    <td align="right"><strong>{{ number_format($coopSavingtotal + $coopLoantotal, 2, '.', ',') }}</strong></td>
    <td align="right"><strong>{{  number_format($salAdvancetotal , 2, '.', ',') }}</strong></td>



    <td align="right"><strong>{{ number_format($deducttotal, 2, '.', ',') }}</strong></td>

     <td align="right"><strong>{{ number_format($totalNetEmolu, 2, '.', ',') }}</strong></td>

  </tr>
</table>

<div class="pull-right">
<p>------------------------------20-------------------------------------------</p>
<p class="text-right" style="margin-right:50px;">SIGNATURE</p>
<br/>
<p>---------------------------------------------------------------------------</p>
<p class="text-center">PAYING OFFICER STAMP</p>
</div>

<h2 class="hidden-print" style="margin-top:10px; color:green; margin-left:20px;">KEY</h2>
<!--Table Key -->

<!--<table class="table-condense table-responsive hidden-print" border="1" cellpadding="4" cellspacing="0" style="margin-left:20px;">
  <tr>
    <th scope="col">ABBREVIATION</th>
    <th scope="col">MEANING</th>
  </tr>
  <tr>
    <td>BS</td>
    <td>Basic Salary</td>
  </tr>

  <tr>
    <td>PEN</td>
    <td>Pension</td>
  </tr>
  <tr>
    <td>NHF</td>
    <td>National Housing Fund</td>
  </tr>

  </table>-->
</div>
    <!-- Table Key -->


    <br>
    <div  style="margin-left:30px;">
      <h2 class="hidden-print">  <a  class="hidden-print" type="submit" class="btn btn-success btn-sm pull-right" href = "{{ url('/payrollReport/create') }}">Back</a>
      </h2>
    </div>

</body>
</html>
