<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<link rel="shortcut icon" href="images/favicon.ico">
<title>ISALU HOSPITAL LIMITED...::...Payroll Summary Voucher</title>
<link rel="stylesheet" href="{{ asset('assets/css/bootstrap.min.css') }}">
<style type="text/css">

body,td,th {
  font-size: 15px;
  font-family: Verdana, Geneva, sans-serif;
  margin:15px;
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
<h4>ANALYSIS OF STAFF SALARIES</h4>
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
    $stafftotal = 0;
    $Earntotal = 0;
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
  @foreach($group as $list)
  
  <tr>
    <td >{{$sn++}}</td>
    <td ></td>
    <td><?php $stafftotal += $list->totalStaff;?>{{$list->totalStaff}}</td>
    <td >{{$list->bank}}</td>
    <td  ><?php $bstotal += $list->basic;?>{{number_format($list->basic,2)}}</td>
    <td><?php $totalAllowance += $list->totAllowance;?>{{number_format($list->totAllowance,2)}}</td>

    <td><?php $Earntotal += $list->totalEarn;?>{{number_format($list->totalEarn,2)}}</td>
    <td><?php $taxtotal += $list->tax;?>{{number_format($list->tax,2)}}</td>
    <td><?php $nhftotal += $list->nhf;?>{{number_format($list->nhf,2)}}</td>
    <td><?php $uniontotal += $list->dues;?>{{number_format($list->dues,2)}}</td>
    <td><?php $pentotal += $list->pension;?>{{number_format($list->pension,2)}}</td>
    <td><?php $cooptotal += $list->coop;?> {{number_format($list->coop,2)}} </td>
    <td></td>
    <td ></td>
    <td><?php $deducttotal += $list->totdeduct;?>{{number_format($list->totdeduct,2)}}</td>
    <td><?php $netpaytotal += $list->netpay;?>{{number_format($list->netpay,2)}}</td>
  </tr>
  
  @endforeach
    <tr>
    <td colspan="2"><strong>Total</strong></td>
    <td  ><strong>{{$stafftotal}}</strong></td>
    <td  ><strong></strong></td>
    
    <td  ><strong>{{number_format($bstotal,2)}}</strong></td>
    <td><strong>{{number_format($totalAllowance,2)}}</strong></td>

    <td><strong>{{number_format($Earntotal,2)}}</strong></td>
    <td><strong>{{number_format($taxtotal,2)}}</strong></td>
    <td><strong>{{number_format($nhftotal,2)}}</strong></td>
    <td><strong>{{number_format($uniontotal,2)}}</strong></td>
    <td><strong>{{number_format($pentotal,2)}}</strong></td>
    <td><strong> {{number_format($cooptotal,2)}} </strong></td>
    <td></td>
    <td ></td>
    <td><strong>{{number_format($deducttotal,2)}}</strong></td>
    <td><strong>{{number_format($netpaytotal,2)}}</strong></td>
  </tr>
  </table>
  
  <p>&nbsp;</p>
</div>
<br />
<br>
         
  <div >
    <h2>  <a  class= "no-print" type="submit" class="btn btn-success btn-sm pull-right" href = "{{ url('/summary/create') }}">Back</a>
    </h2>
    </div>
</body>
</html>
