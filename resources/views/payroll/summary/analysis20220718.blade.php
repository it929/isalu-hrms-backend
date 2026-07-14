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
    $alihsantotal = 0;
    $totalDeduction = 0.00;
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
     <td><strong> AL-IHSAN </strong></td>
    <td><strong> SALARY ADV </strong></td>
    <td><strong> REFUNDS </strong></td>
    <td><strong> TOTAL DEDUCTION </strong></td>
    <td><strong> TOTAL NET <br/> EMOLUMENTS </strong></td>
  </tr>
  @php 
    $sn = 1; 
  
  @endphp
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
     $getSOT = DB::table('tblpayment_consolidated')->where('tblpayment_consolidated.rank','!=',2)->where('month','=',$month)->where('year','=',$year)->where('bank','=',$list->bankid)->sum('SOT');

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
    
    $volPen = DB::table('tblotherEarningDeduction')
    ->leftJoin('tblpayment_consolidated','tblpayment_consolidated.staffid','=','tblotherEarningDeduction.staffid')
     ->where('tblotherEarningDeduction.month','=',$month)
     ->where('tblotherEarningDeduction.year','=',$year)
     ->where('tblpayment_consolidated.month','=',$month)
     ->where('tblpayment_consolidated.year','=',$year)
     ->where('tblotherEarningDeduction.CVID','=',27)
     ->where('tblpayment_consolidated.bank', '=',$list->bankid)
    ->sum('tblotherEarningDeduction.amount');
    
    $alihsan1 = DB::table('tblotherEarningDeduction')
    ->leftJoin('tblpayment_consolidated','tblpayment_consolidated.staffid','=','tblotherEarningDeduction.staffid')
     ->where('tblotherEarningDeduction.month','=',$month)
     ->where('tblotherEarningDeduction.year','=',$year)
     ->where('tblpayment_consolidated.month','=',$month)
     ->where('tblpayment_consolidated.year','=',$year)
     ->where('tblotherEarningDeduction.CVID','=',31)
     ->where('tblpayment_consolidated.bank', '=',$list->bankid)
    ->sum('tblotherEarningDeduction.amount');
    
    $alihsan2 = DB::table('tblotherEarningDeduction')
    ->leftJoin('tblpayment_consolidated','tblpayment_consolidated.staffid','=','tblotherEarningDeduction.staffid')
     ->where('tblotherEarningDeduction.month','=',$month)
     ->where('tblotherEarningDeduction.year','=',$year)
     ->where('tblpayment_consolidated.month','=',$month)
     ->where('tblpayment_consolidated.year','=',$year)
     ->where('tblotherEarningDeduction.CVID','=',33)
     ->where('tblpayment_consolidated.bank', '=',$list->bankid)
    ->sum('tblotherEarningDeduction.amount');
  
  @endphp
  
  
  <tr>
    <td >{{$sn++}}</td>
    <td ></td>
    <td><?php $stafftotal += $totalStaff;?>{{$totalStaff}}</td>
    <td >{{$list->bank}}</td>
    @php 
        $getTotalGrossSum = 0.0;
        $ov = 0.0;
        $sa = 0.0;
        $medAll_ = 0.0;
        
        $bs = 0.0;
        $AEarn = 0.0; 
        $SOT = 0.0;
        $PEC = 0.0;
        $data['payroll_detail_new'] = [];
        
        $data['payroll_detail_new'] = DB::table('tblpayment_consolidated')
          ->where('tblpayment_consolidated.month',     '=', $list->month)
          ->where('tblpayment_consolidated.year',      '=', $list->year)
          ->where('tblpayment_consolidated.rank','!=',2)
          ->where('tblpayment_consolidated.bank',      '=', $list->bankid)
          ->get();
          
        foreach($data['payroll_detail_new'] as $listVal)
        {
            
             $ListH= DB::Select("SELECT IFNULL(sum(`amount`),0) as Taxable FROM `tblotherEarningDeduction` WHERE `CVID`=4 and `staffid`='$listVal->staffid' and `month`='$listVal->month' and `year`='$listVal->year'");
        	 if($ListH){
        	    $h= $ListH[0]->Taxable;
        	 }else{$h = 0.0;}
        	 $ListCa= DB::Select("SELECT IFNULL(sum(`amount`),0) as Taxable FROM `tblotherEarningDeduction` WHERE `CVID`=22 and `staffid`='$listVal->staffid' and `month`='$listVal->month' and `year`='$listVal->year'");
        	   if($ListCa){
        	   $ca = $ListCa[0]->Taxable;
        	 }else{$ca = 0.0;}
        	 
        	 
            $overtime = DB::table('tblotherEarningDeduction')->where('month','=',$listVal->month)->where('year','=',$listVal->year)->where('CVID','=',13)->value('amount');
            $secAllow = DB::table('tblotherEarningDeduction')->where('month','=',$listVal->month)->where('year','=',$listVal->year)->where('CVID','=',17)->value('amount');
            if(count($overtime) == 0){
                $ov += 0;
            }else{
               $ov += $overtime;
            }
             if(count($secAllow) == 0) {
                $sa += 0;
            }else{
               $sa += $secAllow;
            }
            if($listVal->employment_type == 5 || $listVal->employment_type == 8 || $listVal->employment_type == 9){
                $medAll_ += ($ca + $h); 
            }else{
                $medAll_ += 0.00;
            }
            $bs += $listVal->Bs;
            $AEarn += $listVal->AEarn;
            $SOT += $listVal->SOT;
            $PEC += $listVal->PEC;
            
        }
        $getTotalGrossSum = $basic + $totArr + $getSOT + $PEC + $medAll_ + $sa + $ov;    //($listVal->Bs + $listVal->AEarn + $medAll_ + $listVal->SOT + $ov + $sa + $listVal->PEC);
        

    @endphp
            
    <td  ><?php $bstotal += $basic;?>{{number_format($basic,2)}}</td>
    <td><?php $totalAllowance += (($netpay+$totdeduct)-$basic);?>{{number_format((($netpay+$totdeduct)-$basic),2)}}</td>	{{-- (($netpay+$totdeduct)-$basic) $totAllowance--}}	 
    <td><?php $Earntotal += ($netpay+$totdeduct);?>{{number_format(($netpay+$totdeduct),2)}}</td> {{-- ($netpay+$totdeduct) --}} {{-- $totalEarn --}}
    <td><?php $taxtotal += $tax;?>{{number_format($tax,2)}}</td>
    <td><?php $nhftotal += $nhf;?>{{number_format($nhf,2)}}</td>
    <td><?php $uniontotal += $dues;?>{{number_format($dues,2)}}</td>
    <td><?php $pentotal += $pension + $volPen;?>{{number_format($pension + $volPen,2)}}</td>  {{-- $volPen --}}
    <td><?php $cooptotal += $coop2 + $coop1;?> {{number_format(($coop2 + $coop1),2)}} </td> {{-- $volPen --}}
    <td><?php $alihsantotal += $alihsan2 + $alihsan1;?> {{number_format($alihsan1 + $alihsan2,2)}} </td>
    <td><?php $totalAdv += $salAdv;?> {{number_format($salAdv,2)}}</td>
    <td ><?php $totalRefunds += $refunds;?> {{number_format($refunds,2)}}</td>
     @php 
        $totalDeduction = $tax + $nhf + $dues + ($pension + $volPen) + ($coop2 + $coop1) + ($alihsan2 + $alihsan1) + $salAdv + $refunds;
     @endphp
    <td><?php $deducttotal += $totalDeduction;?>{{ number_format($totalDeduction, 2) }} {{-- number_format($totdeduct,2) --}}</td> {{-- $totdeduct --}}
    <td><?php $netpaytotal += $netpay;?>{{number_format($netpay,2)}}</td>
  </tr>
  @endforeach

   <tr>
    <td colspan="2"><strong>Total</strong></td>
    <td  ><strong>{{$stafftotal}}</strong></td>
    <td  ><strong></strong></td>
    
    <td  ><strong>{{number_format($bstotal,2)}}</strong></td>
    <!--<td><strong>{{number_format($totalAllowance,2)}}</strong></td>-->
  <td><strong>{{number_format((($netpaytotal+$deducttotal)-$bstotal),2)}}</strong></td> {{-- ($totalAllowance) --}}
    <td><strong>{{number_format($Earntotal,2)}}</strong></td> {{--  ($netpaytotal+$deducttotal) --}}
    <td><strong>{{number_format($taxtotal,2)}}</strong></td>
    <td><strong>{{number_format($nhftotal,2)}}</strong></td>
    <td><strong>{{number_format($uniontotal,2)}}</strong></td>
    <td><strong>{{number_format($pentotal,2)}}</strong></td>
    <td><strong> {{number_format($cooptotal,2)}} </strong></td>
    <td><strong> {{number_format($alihsantotal,2)}} </strong></td>
    
    <td><strong> {{number_format($totalAdv ,2)}} </strong></td>
    <td ><strong> {{number_format($totalRefunds ,2)}} </strong></td>
    <td>
       <strong>{{number_format($deducttotal,2) }}</strong>
    </td>
    <td><strong>{{number_format($netpaytotal,2)}}</strong></td>
  </tr>
    
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
