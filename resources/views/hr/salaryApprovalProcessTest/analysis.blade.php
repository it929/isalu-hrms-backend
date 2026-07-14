    <!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<link rel="shortcut icon" href="images/favicon.ico">
<title>ISALU HOSPITAL LIMITED</title>
<link rel="stylesheet" href="{{ asset('assets/css/bootstrap.min.css') }}">
<style type="text/css">

body,td,th {
  font-size: 15px;
  font-family: Verdana, Geneva, sans-serif;
  margin:15px;
}
.tables tr td, .tables
{
padding:6px;
border:1px solid #333;
}
@media print
{
    .no-print, .no-print *
    {
        display: none !important;
    }
}

body {
  background-image: {{asset('Images/nicn_bg.jpg')}};
}
.style2 {color: #008000}

</style>
<style type="text/css">
.head-color tr td, .table .th-row td
{
//color:#06c;

}
.table, .table tr td
{
//border: 1px solid #06C;
//color:#06c;
}
</style>
 <script type="text/javascript" src="{{ asset('assets/js/number_to_word.js') }}">
</script>
<body background="{{asset('Images/nicn_bg.jpg')}}"  onload="lookup(); gross();">
<div align="center"><strong><span class="style2"><h3><br />
  <br />
  <br />
ISALU HOSPITAL LIMITED</h3>
<h4>ANALYSIS OF STAFF SALARIES FOR THE MONTH OF {{$month}} {{$year}}</h4>
</span>
        </strong></div>
<br />


  @php
    $bstotal = 0.00;
    $grosstotal = 0.00;
    $jusutotal = 0;
    $taxtotal = 0;
    $pentotal = 0;
    $nhftotal = 0;
    $otherearntotal = 0;
    $earntotal = 0;
    $deducttotal = 0;
    $netpaytotal = 0;
    $uniontotal = 0;
    $totalNetEmolu = 0;
    $totalAllowance =0;
    $cooptotal = 0;
    $hstotal = 0;
    $saladvtotal = 0;
    $stafftotal = 0;
    $Earntotal = 0;
    $totalAdv = 0;
    $totalRefunds = 0;
  @endphp

<div align="left"><strong>

</strong></div><div align="right">

  <table class="table table-condense table-responsive" width="1586" border="1" align="center" cellpadding="0" cellspacing="0">
   <tr>
    <td><strong>SN</strong></td>
    <td><strong>PV No</strong></td>
    <td><strong>NO. OF STAFF</strong></td>
    <td><strong>BANK</strong></td>
    <td><strong>BASIC</strong></td>
    <td><strong>TOTAL <br> ALLOWANCE</strong></td>

    <td><strong>GROSS PAY</strong></td>
    <td><strong>PAYE</strong></td>
    <td><strong> NHF </strong></td>
    <td><strong> U/DUES </strong></td>
    <td><strong> PENSION </strong></td>
    <td><strong> COOPER </strong></td>
    <td><strong> SALARY ADV </strong></td>
    <td><strong> REFUNDS </strong></td>
    <td><strong> TOTAL DEDUCTION </strong></td>
    <td><strong> TOTAL NET <br/> EMOLUMENTS </strong></td>
  </tr>
  @php $sn = 1; @endphp
  @if($group != '')
  @foreach($group as $list)

  @php

     $basic = DB::table('tblpayment_consolidated')->where('tblpayment_consolidated.rank','!=',2)->where('month','=',$month)->where('year','=',$year)->where('bank','=',$list->bankid)->sum('BS');
     $netpay = DB::table('tblpayment_consolidated')->where('tblpayment_consolidated.rank','!=',2)->where('month','=',$month)->where('year','=',$year)->where('bank','=',$list->bankid)->sum('NetPay');
     $totdeduct = DB::table('tblpayment_consolidated')->where('tblpayment_consolidated.rank','!=',2)->where('month','=',$month)->where('year','=',$year)->where('bank','=',$list->bankid)->sum('TD');
     $jusu = DB::table('tblpayment_consolidated')->where('tblpayment_consolidated.rank','!=',2)->where('month','=',$month)->where('year','=',$year)->where('bank','=',$list->bankid)->sum('PEC');
     $pension = DB::table('tblpayment_consolidated')->where('month','=',$month)->where('year','=',$year)->where('bank','=',$list->bankid)->sum('PEN');
     $dues = DB::table('tblpayment_consolidated')->where('tblpayment_consolidated.rank','!=',2)->where('month','=',$month)->where('year','=',$year)->where('bank','=',$list->bankid)->sum('UD');
     $tax = DB::table('tblpayment_consolidated')->where('tblpayment_consolidated.rank','!=',2)->where('month','=',$month)->where('year','=',$year)->where('bank','=',$list->bankid)->sum('TAX');
     $nhf = DB::table('tblpayment_consolidated')->where('tblpayment_consolidated.rank','!=',2)->where('month','=',$month)->where('year','=',$year)->where('bank','=',$list->bankid)->sum('NHF');
     $totAllowance = DB::table('tblpayment_consolidated')->where('tblpayment_consolidated.rank','!=',2)->where('month','=',$month)->where('year','=',$year)->where('bank','=',$list->bankid)->sum('OEarn');
     $totArr = DB::table('tblpayment_consolidated')->where('tblpayment_consolidated.rank','!=',2)->where('month','=',$month)->where('year','=',$year)->where('bank','=',$list->bankid)->sum('OEarn');
     $totalEarn = DB::table('tblpayment_consolidated')->where('tblpayment_consolidated.rank','!=',2)->where('month','=',$month)->where('year','=',$year)->where('bank','=',$list->bankid)->sum('TEarn');
     $coop = DB::table('tblpayment_consolidated')->where('tblpayment_consolidated.rank','!=',2)->where('month','=',$month)->where('year','=',$year)->where('bank','=',$list->bankid)->sum('OD');
     $totalStaff = DB::table('tblpayment_consolidated')->where('tblpayment_consolidated.rank','!=',2)->where('month','=',$month)->where('year','=',$year)->where('bank','=',$list->bankid)->count();

     $coop1 = DB::table('tblotherEarningDeduction')
    ->leftJoin('tblpayment_consolidated','tblpayment_consolidated.staffid','=','tblotherEarningDeduction.staffid')
     ->where('tblotherEarningDeduction.month','=',$month)
     ->where('tblotherEarningDeduction.year','=',$year)
     ->where('tblpayment_consolidated.month','=',$month)
     ->where('tblpayment_consolidated.year','=',$year)
     ->where('tblotherEarningDeduction.CVID','=',15)
     ->where('tblpayment_consolidated.bank', '=',$list->bankid)
     ->sum('tblotherEarningDeduction.amount');

    $coop2 = DB::table('tblotherEarningDeduction')
    ->leftJoin('tblpayment_consolidated','tblpayment_consolidated.staffid','=','tblotherEarningDeduction.staffid')
     ->where('tblotherEarningDeduction.month','=',$month)
     ->where('tblotherEarningDeduction.year','=',$year)
     ->where('tblpayment_consolidated.month','=',$month)
     ->where('tblpayment_consolidated.year','=',$year)
     ->where('tblotherEarningDeduction.CVID','=',16)
     ->where('tblpayment_consolidated.bank', '=',$list->bankid)
    ->sum('tblotherEarningDeduction.amount');

    $salAdv = DB::table('tblotherEarningDeduction')
    ->leftJoin('tblpayment_consolidated','tblpayment_consolidated.staffid','=','tblotherEarningDeduction.staffid')
     ->where('tblotherEarningDeduction.month','=',$month)
     ->where('tblotherEarningDeduction.year','=',$year)
     ->where('tblpayment_consolidated.month','=',$month)
     ->where('tblpayment_consolidated.year','=',$year)
     ->where('tblotherEarningDeduction.CVID','=',18)
     ->where('tblpayment_consolidated.bank', '=',$list->bankid)
    ->sum('tblotherEarningDeduction.amount');

    $refunds = DB::table('tblotherEarningDeduction')
    ->leftJoin('tblpayment_consolidated','tblpayment_consolidated.staffid','=','tblotherEarningDeduction.staffid')
     ->where('tblotherEarningDeduction.month','=',$month)
     ->where('tblotherEarningDeduction.year','=',$year)
     ->where('tblpayment_consolidated.month','=',$month)
     ->where('tblpayment_consolidated.year','=',$year)
     ->where('tblotherEarningDeduction.CVID','=',2)
     ->where('tblpayment_consolidated.bank', '=',$list->bankid)
    ->sum('tblotherEarningDeduction.amount');

  @endphp


  <tr>
    <td >{{$sn++}}</td>
    <td ></td>
    <td><?php $stafftotal += $totalStaff;?>{{$totalStaff}}</td>
    <td >{{$list->bank}}</td>
    <td  ><?php $bstotal += $basic;?>{{number_format($basic,2)}}</td>

<td><?php $totalAllowance += $totAllowance;?>{{number_format((($netpay+$totdeduct)-$basic),2)}}</td>
    <td><?php $Earntotal += $totalEarn;?>{{number_format(($netpay+$totdeduct),2)}}</td>
    <td><?php $taxtotal += $tax;?>{{number_format($tax,2)}}</td>
    <td><?php $nhftotal += $nhf;?>{{number_format($nhf,2)}}</td>
    <td><?php $uniontotal += $dues;?>{{number_format($dues,2)}}</td>
    <td><?php $pentotal += $pension;?>{{number_format($pension,2)}}</td>
    <td><?php $cooptotal += $coop2 + $coop1;?> {{number_format($coop2 + $coop1,2)}} </td>
    <td><?php $totalAdv += $salAdv;?> {{number_format($salAdv,2)}}</td>
    <td ><?php $totalRefunds += $refunds;?> {{number_format($refunds,2)}}</td>
    <td><?php $deducttotal += $totdeduct;?>{{number_format($totdeduct,2)}}</td>
    <td><?php $netpaytotal += $netpay;?>{{number_format($netpay,2)}}</td>
  </tr>

  @endforeach

   <tr>
    <td colspan="2"><strong>Total</strong></td>
    <td  ><strong>{{$stafftotal}}</strong></td>
    <td  ><strong></strong></td>

    <td  ><strong>{{number_format($bstotal,2)}}</strong></td>
    <!--<td><strong>{{number_format($totalAllowance,2)}}</strong></td>-->
  <td><strong>{{number_format((($netpaytotal+$deducttotal)-$bstotal),2)}}</strong></td>
    <td><strong>{{number_format(($netpaytotal+$deducttotal),2)}}</strong></td>
    <td><strong>{{number_format($taxtotal,2)}}</strong></td>
    <td><strong>{{number_format($nhftotal,2)}}</strong></td>
    <td><strong>{{number_format($uniontotal,2)}}</strong></td>
    <td><strong>{{number_format($pentotal,2)}}</strong></td>
    <td><strong> {{number_format($cooptotal,2)}} </strong></td>
    <td><strong> {{number_format($totalAdv ,2)}} </strong></td>
    <td ><strong> {{number_format($totalRefunds ,2)}} </strong></td>
    <td><strong>{{number_format($deducttotal,2)}}</strong></td>
    <td><strong>{{number_format($netpaytotal,2)}}</strong></td>
  </tr>
  @else

  <tr>
    <td colspan="15" class="text-center"><strong>No Record Available at the moment</strong></td>
  </tr>

    @endif
  </table>

  <p>&nbsp;</p>
</div>


<table width="1100" border="1" class="tables sign">

<tr>
<td width="150"> <strong> Prepared By</strong></td>
<td width="450">&nbsp;</td>
<td width="250">&nbsp;</td>
<td width="200">&nbsp;</td>
</tr>
<tr>
<td width="150"> <strong> Checked By</strong></td>
<td width="450">&nbsp;</td>
<td width="250">&nbsp;</td>
<td width="200">&nbsp;</td>
</tr>
<tr>
<td width="150"> <strong> Audited By</strong></td>
<td width="450">&nbsp;</td>
<td width="250">&nbsp;</td>
<td width="200">&nbsp;</td>
</tr>

</table>

  <div >
    <h2>  <a  class= "no-print" type="submit" class="btn btn-success btn-sm pull-right" href = "{{ url('/summary/create') }}">Back</a>
    </h2>
    </div>
</body>
</html>
