<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
  <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
  <link rel="shortcut icon" href="images/favicon.ico">
  <title>ISALU HOSPITAL LIMITED PAYROLL
    ...::...Payroll Report</title>

<link rel="stylesheet" href="{{ asset('assets/css/bootstrap.min.css') }}">
<style type="text/css">
.pr
{
display:none;
}
</style>
<style type="text/css" media="print">
.pr
{
display:block;
}


.sticky  {
    position: fixed;
  top: 0;
  z-index: 10;
}


</style>
  <script type="text/javascript" src="{{ asset('assets/js/number_to_word.js') }}">
  </script>
</head>


<body>
<div align="center"><h2><div style="color:#06c;">ISALU HOSPITAL LIMITED
      <br />
      VARIATION CONTROL FOR THE FOR THE MONTH ENDING.............................</div><br />
  </h2>
  <h3 align="center"> </h3>
</div>

<div style="width:90%; margin: auto;">
<table class="head-color" width="1802" border="0" cellpadding="0" cellspacing="0" style="font-size:18px" >
  <tr>
    <td>Payroll P.V. No:</td>
    <td><div align="left">Sheet No:</div></td>
  </tr>
  <tr>
    <td colspan="2"><h3>MINISTRY/DEPARTMENT:
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


<table class="table table-condense table-responsive " border="1" cellpadding="4" cellspacing="0">
  <tr class="th-row sticky">
    <td class="sticky" width="44" rowspan="2" align="center" valign="middle"><strong>SN</strong></td>
    <td class="sticky" width="44" rowspan="2" align="center" valign="middle"><strong>FILE NO</strong></td>
    <td class="sticky" width="271" rowspan="2" align="center" valign="middle"><strong>NAME</strong></td>
    <td class="sticky" width="23" rowspan="2" align="center" valign="middle"><strong>GL</strong></td>
    <td class="sticky" width="23" rowspan="2" align="center" valign="middle"><strong>ST</strong></td>
    <td class="sticky" width="23" rowspan="2" align="center" valign="middle"><strong>NEW GL/S</strong></td>
    <td class="sticky" colspan="6" align="center" valign="top"><strong>EARNINGS</strong></td>
    
    
    <td class="sticky" rowspan="2" align="center" valign="top"><strong>JUSUN</strong></td>
    <td class="sticky" rowspan="2" align="center" valign="top"><strong>TOTAL AMOUNT</strong></td>
    
    
    <td class="sticky" colspan="4" align="center" valign="top"><strong>DEDUCTIONS</strong></td>
     <td class="sticky" width="70" rowspan="2" align="center" valign="middle"><strong>TOTAL ARREARS DEDUCTION.</strong></td>
    <td class="sticky" width="70" rowspan="2" align="center" valign="middle"><strong>TOTAL DEDUCTION.</strong></td>
    
    <td class="sticky" width="75" rowspan="2" align="center" valign ="middle"><strong> </strong></td>
    <td class="sticky" width="75" rowspan="2" align="center" valign ="middle"><strong> TOTAL NET <BR/> EMOLUMENT </strong></td>
    
  </tr>
  <tr class="sticky">
  <!--EARNING -->
    <td class="sticky" width="90" align="center" valign="middle"><strong>GROSS</strong></td>
     <td class="sticky" width="80" align="center" valign="middle"><strong>ACTUAL</strong></td>
    <td class="sticky" width="38" align="center" valign="middle"><strong>ARREARS </strong></td>
    <td class="sticky" width="38" align="center" valign="middle"><strong>JUSUN <br/> ARREARS</strong></td>
    <td class="sticky" width="38" align="center" valign="middle"><strong>TOTAL ARREARS </strong></td>
    <td class="sticky" width="52" align="center" valign="middle"><strong>OVERTIME/<BR/>ACTING ALL.</strong></td>
    
   
  <!--///EARNING -->
  
  <!-- DEDUCTIONS-->
    <td class="sticky" width="74" align="center" valign="middle"><strong>TAX</strong></td>
    <td class="sticky" width="50" align="center" valign="middle"><strong>PENSION</strong></td>
     <td class="sticky" width="50" align="center" valign="middle"><strong>UNION DUES</strong></td>
    <td class="sticky" width="85" align="center" valign="middle"><strong>NHF</strong></td>
    <!--// DEDUCTIONS-->
    
    
 </tr>

  @php
  function dateDiff($date2, $date1)
	{
		list($year2, $mth2, $day2) = explode("-", $date2);
		list($year1, $mth1, $day1) = explode("-", $date1);
		if ($year1 > $year2) dd('Invalid Input - dates do not match');
		//$days_month = 0;
		$days_month = cal_days_in_month(CAL_GREGORIAN, $mth1, $year1);
		$day_diff = 0;

		if($year2 == $year1){
			$mth_diff = $mth2 - $mth1;
		}
		else{
			$yr_diff = $year2 - $year1;
			$mth_diff = (12 * $yr_diff) - $mth1 + $mth2;
		}
		if($day1 > 1){
			$mth_diff--;
			//dd($mth1.",".$year1);
			$day_diff = $days_month - $day1 + 1;
		}

		$result = array('months'=>$mth_diff, 'days'=> $day_diff, 'days_of_month'=>$days_month);
		return($result);
	}
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
    $realArrtotal = 0;
    $actualtotal = 0;
    $oldtotal = 0;
    $jusuArrtotal = 0;
    $totalarears = 0;
    $overTimetotal = 0;
    $pecoldtotal = 0;
    $arrdeducttotal =0;
    $k=1;
  @endphp


  @foreach ($payroll_detail as $reports)
    @php
      $fileNo = str_replace("/", "-", $reports->fileNo);
    @endphp
    <tr>
    <td align="right" >{{ $k++ }}</td>
      <td align="right" >{{ $reports->fileNo }}</td>
      <td align="left" valign="middle" nowrap="nowrap"><a class="hidden-print" target ="_blank" href="{{url("/con-pecard/getCard/$reports->staffid/$reports->year")}}">{{ $reports->name }}</a> <span class="pr"> {{ $reports->name }} </span></td>
      
        <?php
       
        $actual = 0;
        $total = 0;
        $arrDeduct = 0;
        $realArr = 0;
        $jusuarr =0;
        $monthsDiff =0;
        $unionarr =0;
        $actualUnion =0;
        $actualPen =0;
        $actualNhf =0;
        $actualTax =0;
        $ovTime = 0;
        $totDeduction=0;
          $overTime = DB::table('tblotherEarningDeduction')->where('staffid','=',$reports->staffid)->where('month','=',$reports->month)->where('year','=',$reports->year)->where('CVID','=',13)->first();
           $check = DB::table('tblarrears')->where('staffid','=',$reports->staffid)->where('month','=',$reports->month)->where('year','=',$reports->year)->first();
           if($overTime !='')
     {
     $ovTime = $overTime->amount;
     }
      if($reports->AEarn != '' && $check != '')
      {
      $arr = DB::table('tblarrears')->where('staffid','=',$reports->staffid)->where('month','=',$reports->month)->where('year','=',$reports->year)->first();
          
      
      $actual = $arr->newBasic-$arr->oldBasic;
      $actualJusu = $arr->newPeculiar - $arr->oldPeculiar;
      $actualTax = $arr->newTax - $arr->oldTax;
      $actualUnion = $arr->newUnionDues - $arr->oldUnionDues;
      $actualNhf = $arr->newNhf - $arr->oldNhf;
      $actualPen = $arr->newPension - $arr->oldPension;
      
      $monthsDiff = dateDiff($activeDate, $arr->dueDate);
      $numMonths = $monthsDiff['months'];
      
      $jusuarr = ($numMonths * $actualJusu) + $actualJusu;
      $realArr = $numMonths * $actual;
      $taxarr = ($numMonths * $actualTax) + $actualTax;
      $unionarr = ($numMonths * $actualUnion) + $actualUnion;
      $nhfarr = ($numMonths * $actualNhf) + $actualNhf;
      $penarr = ($numMonths * $actualPen) + $actualPen;
      
      $actualDeduct = $actualTax + $actualUnion + $actualNhf + $actualPen;
      $arrDeduct = $unionarr + $taxarr + $nhfarr + $penarr ;
      
      $arrEarnTotal = $realArr + $jusuarr + $actual ;
      
       $total = $arr->oldBasic + $arr->oldPeculiar;
       $totDeduction = $reports->TD- $arrDeduct;
      }
      else
      {
      $total = $reports->Bs + $reports->PEC;
       $totDeduction = $reports->TD;
      }
      
    
      
      ?>
       @if($reports->AEarn == "")
      <td width="23" align="center" valign="middle"> {{ $reports->grade }}</td>
      <td width="23" align="center" valign="middle">{{$reports->step}}</td>
      @else
       <td width="23" align="center" valign="middle"> {{ $arr->oldGrade }}</td>
       <td width="23" align="center" valign="middle">{{$arr->OldStep}}</td>
      @endif
      @if($reports->AEarn == "")
      <td width="23" align="center" valign="middle"> </td>
      
      @else
       <td width="23" align="center" valign="middle"> {{ $reports->grade }}/{{$reports->step}}</td>
       
      @endif
      
      
      
      <!--EARNING -->
      @if($reports->AEarn == "")
      <td width="75" align="right"><?php $bstotal += $reports->Bs; ?> {{number_format($reports->Bs, 2, '.', ',')}} </td>
      @else
      <td width="75" align="right"><?php $bstotal += $arr->oldBasic; ?> {{number_format($arr->oldBasic, 2, '.', ',')}}</td>
      @endif
      <td width="80" align="right"><?php $actualtotal += $actual;?> <strong>{{number_format($actual, 2, '.', ',')}}</strong></td>
      @if($reports->AEarn == "")
        <td width="66" align="right"><?php $e_arrearstotal += $reports->AEarn;?></td>
         <td width="85" align="center"></td>
        <td width="66" align="right"></td>
      @else
      <td width="66" align="right"><?php $realArrtotal += $realArr;?>{{number_format($realArr, 2, '.', ',')}}</td>
       <td width="85" align="center"><?php $jusuArrtotal += $jusuarr;?> {{number_format($jusuarr, 2, '.', ',')}} </td>
        <td width="66" align="right"><?php $totalarears += $arrEarnTotal;?>{{number_format($arrEarnTotal, 2, '.', ',')}}</td>
      @endif
      <td width="38" align="right"><?php $overTimetotal += $ovTime;?>@if($overTime !='') {{number_format($overTime->amount, 2, '.', ',')}}@endif</td>
     
      <!--///EARNING -->
      
      @if($reports->AEarn =='')
     
     
      <td width="85" align="center"><?php $pectotal += $reports->PEC;?> {{number_format($reports->PEC, 2, '.', ',')}}</td>
      @else
      
      <td width="85" align="center"><?php $pecoldtotal += $arr->oldPeculiar;?> {{number_format($arr->oldPeculiar, 2, '.', ',')}}</td>
      @endif
      
      <td width="85" align="center"><?php $oldtotal += $total;?>{{number_format($total, 2, '.', ',')}}</td>
      
<!-- Deductions -->
      <td width="52" align="right"><?php $taxtotal += $reports->TAX;?> </td>
      <td width="74" align="right"><?php $pentotal += $reports->PEN;?> </td>
       <td width="74" align="right"><?php $uniontotal += $reports->UD;?> </td>
      <td width="50" align="right"><?php $nhftotal += $reports->NHF;?> </td>
      <!--///Deductions -->

     <td width="82" align="right" ><?php $arrdeducttotal += $arrDeduct;?> <strong> {{number_format($arrDeduct, 2, '.', ',')}}</strong></td>
      <td width="82" align="right" ><?php $deducttotal += $totDeduction;?> <strong> {{number_format($totDeduction, 2, '.', ',')}}</strong></td>
      
      <td width="41" align="right" valign="middle"> </td>
      <td width="82" align="right"><?php $totalNetEmolu += $reports->NetPay;?> {{number_format($reports->NetPay - $arrDeduct - $realArr - $jusuarr-$ovTime , 2, '.', ',')}}</td>
      
     
    </tr>

  @endforeach


  <tr><td colspan="6" align="right"><strong>TOTAL</strong></td>

    <td align="right"><strong>{{ number_format($bstotal, 2, '.', ',') }}</strong></td>
    
        <td align="right"><strong>{{ number_format($actualtotal, 2, '.', ',') }} </strong></td>
    <td align="right"><strong>{{ number_format($realArrtotal, 2, '.', ',') }}</strong></td>
    <td align="right"><strong>{{number_format($jusuArrtotal, 2, '.', ',')}}</strong></td>

<td align="right"><strong>{{  number_format($totalarears, 2, '.', ',') }}</strong></td>
    <td align="right"><strong>{{  number_format($overTimetotal, 2, '.', ',') }}</strong></td>
    <td align="right"><strong> {{  number_format($pectotal + $pecoldtotal , 2, '.', ',') }} </strong></td>
    <td align="right"><strong> {{  number_format($oldtotal, 2, '.', ',') }} </strong></td>
    
    <td align="right"><strong> </strong></td>

    <td align="right"><strong></strong></td>
    <td align="right"><strong></strong></td>
    <td align="right"><strong></strong></td>
   
    <td align="right"><strong>{{ number_format($arrdeducttotal, 2, '.', ',') }}</strong></td>
 <td align="right"><strong>{{ number_format($deducttotal, 2, '.', ',') }}</strong></td>
    <td align="right"><strong></strong></td>
    
     
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