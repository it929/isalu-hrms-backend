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
/* border: 1px solid #06C;
color:#06c; */
}
</style>
 <script type="text/javascript" src="{{ asset('assets/js/number_to_word.js') }}">
</script>
<body background="{{asset('Images/nicn_bg.jpg')}}"  onload="lookup(); gross();">
<div align="center"><strong><span class="style2"><h3><br />
  <br />
  <br />
  ISALU HOSPITAL LIMITED {{ isset($divisionName) ?  ', '. strtoupper($divisionName) : '' }}</h3>
<h4>SUMMARY OF STAFF SALARIES FOR THE MONTH OF {{$month}} {{$year}}</h4>
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
    //$earntotal = 0;
    $deducttotal = 0;
    $netpaytotal = 0;
    $uniontotal = 0;
    $totalNetEmolu = 0;
    $totalAllowancePec =0;
    $totalAllowancePecFG = 0;
    $cooptotal = 0;
    $hstotal = 0;
    $saladvtotal = 0;
    $stafftotal = 0;
    $earntotal = 0;
    $totalAdv = 0;
    $totalRefunds = 0;
    $alihsantotal = 0;
    $totalDynamicEarn = 0;
    $totalDynamicDeduction = 0;
    $totalDynamicEarning = 0.0;
  @endphp

<div align="left"><strong>

</strong></div><div align="right">

  <table class="table table-condense table-responsive" width="1586" border="1" align="center" cellpadding="0" cellspacing="0" id="tableData">
   <tr>
    <td><strong>SN</strong></td>
    <td><strong>PV No</strong></td>
    <td><strong>NO. OF STAFF</strong></td>
    <td><strong>BANK</strong></td>
    <td><strong>BASIC</strong></td>
    @if(isset($staffEarnElement) && $staffEarnElement)
        @foreach($staffEarnElement as $elementEarn)
            <th align="center"><strong>{{strtoupper($elementEarn->description)}}</strong></th>
            @php $totalEarnAmount[$elementEarn->CVID] = 0.0; @endphp
        @endforeach
    @endif
    <td><strong>PECULIAR</strong></td>
    <td><strong>PECULIAR <br> FG</strong></td>
    <td style="background: #dedede;"><strong>GROSS PAY</strong></td>
    <td><strong>PAYE</strong></td>
    <td><strong> NHF </strong></td>
    <td><strong> U/DUES </strong></td>
    <td><strong> PENSION </strong></td>
    @if(isset($staffDeductionElement) && $staffDeductionElement)
      @foreach($staffDeductionElement as $elementDeduct)
          <th align="center"><strong>{{strtoupper($elementDeduct->description)}}</strong></th>
          @php $totalDeductAmount[$elementDeduct->CVID] = 0.0; @endphp
      @endforeach
    @endif
    <td style="background: #dedede;"><strong> TOTAL DEDUCTION </strong></td>
    <td><strong> TOTAL NET <br/> EMOLUMENTS </strong></td>
  </tr>
 
  
  @if(isset($allBanks) && $allBanks)
    @foreach ($allBanks as $key=>$eachBank)
      <tr>
        <td >{{$key + 1}}</td>
        <td></td>
        <td><?php $stafftotal += $eachBank->totalStaffNo;?>{{ $eachBank->totalStaffNo }}</td>
        <td>{{$eachBank->bank_name}}</td>
        <td><?php $bstotal        += ($eachBank->totalBS + $eachBank->totalAEarn);?>{{number_format(($eachBank->totalBS + $eachBank->totalAEarn), 2)}}</td>
        @php $sumEarnAmount = 0.0; $sumDeductAmount = 0.0; $sumEarnGross = 0.0; @endphp
        @foreach ($staffEarnElement as $element) 
            @php 
              $getEarnAmount = $getStaffMonthEarnAmount[$eachBank->bank][$element->CVID]; 
              $sumEarnAmount = $getEarnAmount ? $getEarnAmount->staffEarnings : 0.0;
              $totalEarnAmount[$element->CVID] += $sumEarnAmount;
              $totalDynamicEarn += $sumEarnAmount;
            @endphp
            <td> {{number_format($sumEarnAmount , 2, '.', ',')}}</td>
        @endforeach
        <td><?php $totalAllowancePec += ($eachBank->totalPEC);?>{{number_format(($eachBank->totalPEC), 2)}}</td>
        <td><?php $totalAllowancePecFG += ($eachBank->totalPECFG);?>{{number_format(($eachBank->totalPECFG), 2)}}</td>		
        <td style="background: #dedede;"><?php $earntotal      += $eachBank->totalTEarn;?>{{number_format($eachBank->totalTEarn, 2)}}</td>
        <td><?php $taxtotal       += $eachBank->totalTAX;?>{{number_format($eachBank->totalTAX, 2)}}</td>
        <td><?php $nhftotal       += $eachBank->totalNHF;?>{{number_format($eachBank->totalNHF,2)}}</td>
        <td><?php $uniontotal     += $eachBank->totalUD;?>{{number_format($eachBank->totalUD,2)}}</td>
        <td><?php $pentotal       += $eachBank->totalPEN;?>{{number_format($eachBank->totalPEN,2)}}</td>
        @foreach ($staffDeductionElement as $elementDec) 	
          @php
              $getDeductAmount = $getStaffMonthDeductionAmount[$eachBank->bank][$elementDec->CVID]; 
              $sumDeductAmount = $getDeductAmount ? $getDeductAmount->staffDeductions : 0.0;
              $totalDeductAmount[$elementDec->CVID] += $sumDeductAmount;
              $totalDynamicDeduction += $sumDeductAmount;
          @endphp
          <td>{{number_format($sumDeductAmount, 2, '.', ',')}}</td>
			  @endforeach
        <td style="background: #dedede;"><?php $deducttotal += ($eachBank->totalTD);?>{{number_format($eachBank->totalTD, 2)}}</td>
        <td><?php $netpaytotal += $eachBank->totalNetPay;?>{{number_format($eachBank->totalNetPay,2)}}</td>
      </tr>
    
      @endforeach
    @endif
    
   <tr>
      <td colspan="2"><strong>Total</strong></td>
      <td  ><strong>{{$stafftotal}}</strong></td>
      <td  ><strong></strong></td>
      <td><strong>{{number_format($bstotal,2)}}</strong></td>
      @if(isset($staffEarnElement) && $staffEarnElement)
        @foreach($staffEarnElement as $elementEarn)
            <th align="center"><strong>{{number_format($totalEarnAmount[$elementEarn->CVID], 2)}}</strong></th>
            @php $totalDynamicEarning += $totalEarnAmount[$elementEarn->CVID]; @endphp
        @endforeach
      @endif
      <td><strong>{{number_format($totalAllowancePec,2)}}</strong></td>
      <td><strong>{{number_format($totalAllowancePecFG,2)}}</strong></td>
      <td><strong>{{number_format(($totalAllowancePec + $totalAllowancePecFG + $bstotal + $totalDynamicEarning),2)}}</strong></td>
      <td><strong>{{number_format($taxtotal,2)}}</strong></td>
      <td><strong>{{number_format($nhftotal,2)}}</strong></td>
      <td><strong>{{number_format($uniontotal,2)}}</strong></td>
      <td><strong>{{number_format($pentotal,2)}}</strong></td>
      @if(isset($staffDeductionElement) && $staffDeductionElement)
        @foreach($staffDeductionElement as $elementDeduct)
            <th align="center"><strong>{{number_format($totalDeductAmount[$elementDeduct->CVID], 2)}}</strong></th>
        @endforeach
      @endif
      <td><strong>{{number_format($deducttotal, 2)}}</strong></td>
      <td><strong>{{number_format($netpaytotal,2)}}</strong></td>
  </tr>

      <tr border="0" class="no-print">
          <td colspan="17">
          
          </td>
      </tr>

      
              <tr>
                  <td width="150" colspan="4"> <strong> Prepared By</strong></td>
                  <td width="450" colspan="5">&nbsp;</td>
                  <td width="250" colspan="4">&nbsp;</td>
                  <td width="200" colspan="4">&nbsp;</td>
              </tr>
              <tr>
                  <td width="150" colspan="4"> <strong> Checked By</strong></td>
                  <td width="450" colspan="5">&nbsp;</td>
                  <td width="250" colspan="4">&nbsp;</td>
                  <td width="200" colspan="4">&nbsp;</td>
              </tr>
              <tr>
                  <td width="150" colspan="4"> <strong> Audited By</strong></td>
                  <td width="450" colspan="5">&nbsp;</td>
                  <td width="250" colspan="4">&nbsp;</td>
                  <td width="200" colspan="4">&nbsp;</td>
              </tr>
  </table>
  
  <br />
  <div class="no-print" align="center">
    <input type="button" class="hidden-print" id="btnExport" value="Export to Excel" onclick="ExportToExcel('xlsx')" />    
  </div>
</div>



         
    <div >
      <h2>  
        <a  class= "no-print" type="submit" class="btn btn-success btn-sm pull-right" href = "{{ url('/payroll/analysis') }}">Back</a>
      </h2>
    </div>

    <script src="{{asset('assets/js/jQuery-2.2.0.min.js')}}"></script>
    <script src="{{asset('assets/js/table2excel.js')}}"></script>

      <script type="text/javascript">
        function ExportToExcel() {
          //$("#btnExport").hide();
            $("#tableData").table2excel({
                filename: "{{isset($month) ? $month: ''}}_{{isset($year) ? $year : ''}}-Mandate.xls"
            });
            $("#tableData").excelexportjs({
              containerid: "tableData", datatype: 'table'
            });
            //$("#btnExport").show();
        }
      </script>

</body>
</html>
