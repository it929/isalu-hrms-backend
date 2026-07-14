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

 @media print {
.table tr .bg
{
background:#0cf !important;
opacity:0.8 !important;
color:#FFF !important;
}
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
      COUNCIL MEMBERS PAYROLL</div><br />
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

        ISALU HOSPITAL LIMITED, ABUJA </h3></td>
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
   <td width="44" rowspan="2" align="center" valign="middle"><strong>FileNo</strong></td>
    <td width="271" rowspan="2" align="center" valign="middle"><strong>NAME</strong></td>


    <td colspan="3" align="center" valign="top"><strong>EARNINGS</strong></td>
    <td colspan="1" align="center" valign="top"><strong>DEDUCTIONS</strong></td>


    <td width="70" rowspan="2" align="center" valign="middle"><strong>TOTAL DEDUCTION.</strong></td>
      <td width="85" rowspan="2" align="center" valign="middle"><strong>NET PAY</td>

  </tr>
  <tr>
    <td width="90" align="center" valign="middle"><strong>BASIC <br/> (CONSOLIDATED)</strong></td>

    <td align="center" valign="middle"><strong>ALLOWANCE</strong></td>

    <td width="80" align="center" valign="middle"><strong>GROSS<br/> EMOLUMENT</strong></td>

    <td width="74" align="center" valign="middle"><strong>TAX</strong></td>





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
    $totalSot =0;
    $k = 1;
    $medAllowanceTotal = 0;
    $cooptotal = 0;
    $salAdvancetotal = 0;
    $coopLoantotal =0;
    $totalGross =0;
    $totalAllow = 0;
  @endphp


  @foreach ($payroll_detail as $reports)
    @php

    $severance = DB::table('tblotherEarningDeduction')->where('CVID','=',25)->where('staffid','=', $reports->staffid)->where('year','=', $reports->year)->where('month','=', $reports->month)->first();
    $furAll = DB::table('tblotherEarningDeduction')->where('CVID','=',23)->where('staffid','=', $reports->staffid)->where('year','=', $reports->year)->where('month','=', $reports->month)->first();
    if($severance == '')
    {
     $ser = 0;
    }
    else
    {
      $ser = $severance->amount;
    }
    if($furAll == '')
    {
    $funiture = 0;
    }
    else
    {
    $funiture = $furAll->amount;
    }

    @endphp

    <tr>
    <td align="right" >{{ $k++ }}</td>
      <td align="right" >{{ $reports->fileNo }}</td>
      <td align="left" valign="middle" nowrap="nowrap"><a class="hidden-print" target ="_blank" href="{{url("/con-pecard/getCard/$reports->staffid/$reports->year")}}">{{ $reports->council_title }} {{ $reports->name }}</a> <span class="pr">{{ $reports->council_title }} {{ $reports->name }}</span></td>
      <td width="75" align="right"><?php $bstotal += $reports->Bs; ?> {{number_format($reports->Bs, 2, '.', ',')}}</td>
      <td width="75" align="right"><?php $totalAllow += $reports->PEC + $ser + $funiture; ?> {{number_format($reports->PEC + $ser + $funiture, 2, '.', ',')}}</td>

      <td width="66" align="right"><?php $totalGross += $reports->Bs + $reports->PEC + $ser + $funiture;?>{{number_format($reports->Bs + $reports->PEC + $ser + $funiture, 2, '.', ',')}}</td>

      <td width="52" align="right"><?php $taxtotal += $reports->TD;?> {{number_format($reports->TD, 2, '.', ',')}}</td>

      <td width="82" align="right" class="bg" style="background:#0cf;opacity:0.8;"><?php $deducttotal += $reports->TD;?> <strong> {{number_format($reports->TD, 2, '.', ',')}}</strong></td>

      <td width="82" align="right"><?php $totalNetEmolu += $reports->NetPay;?> {{number_format($reports->NetPay, 2, '.', ',')}}</td>

    </tr>

  @endforeach


  <tr><td colspan="3" align="right"><strong>TOTAL</strong></td>

    <td align="right"><strong>{{ number_format($bstotal, 2, '.', ',') }}</strong></td>

        <td align="right"><strong>{{ number_format($totalAllow, 2, '.', ',') }} </strong></td>
        <td align="right"><strong>{{ number_format($totalGross, 2, '.', ',') }} </strong></td>


    <td align="right"><strong>{{ number_format($taxtotal, 2, '.', ',') }}</strong></td>
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
