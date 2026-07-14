<!DOCTYPE html>
<html>
<head>

  <title>ISALU HOSPITAL LIMITED...::...E-payment Schedule</title>
  <link rel="stylesheet" href="{{ asset('assets/css/bootstrap.min.css') }}">

<style type="text/css">
    <!--
    .style25 {  font-family: Verdana, Arial, Helvetica, sans-serif;
      color: #FF0000;
    }
    a:link {
      text-decoration: none;
    }
    a:visited {
      text-decoration: none;
    }
    a:hover {
      text-decoration: underline;
    }
    a:active {
      text-decoration: none;
    }

  .tblborder{
     border: 1px solid #303030 !important;
  }
  .no-border{
    border: none !important;
    border: 0;
  }
  .table tr td
  {
  padding:2px;
  }

.tblborder {
  border-top-width: 1px;
  border-right-width: 1px;
  border-bottom-width: 1px;
  border-left-width: 1px;
  border-top-style: dotted;
  border-right-style: dotted;
  border-bottom-style: dotted;
  border-left-style: dotted;
}
body, td,th {
  font-size: 15px;
  font-family: Verdana, Geneva, sans-serif;
}
a#otherpages {
  font-size:18px
}
@media  print
{
  .no-print, .no-print *
  {
    display: none !important;
  }
}
-->

select {
    appearance: none;
    -webkit-appearance: none;
    -moz-appearance: none;
    border: none;
    /* needed for Firefox: */
    overflow:hidden;
    width: 60%;
}

.sigtab tr td
{
  padding: 10px;
}
.sigtab p
{
  border: 1px solid #ccc;
  padding: 9px;
  width: 100%;
  margin: 0px;
}

.totext{
  mso-number-format:"\@";/*force text*/
}
</style>
@php
$lock = 0;
@endphp

@if($lock > 0)

@else

<style type="text/css" media="print">
  /* body
    {
        display: none;
        visibility: hidden;
    }*/
</style>

@endif

<script type="text/javascript" src="{{ asset('assets/js/number_to_word.js') }}">
</script>
</head>
<body style="background: url(../Images/watermarks.jpg) repeat !important; -webkit-print-color-adjust: exact;" onload="lookup()">

<div class="col-md-12">
<div class="col-md-12">
<div>
      <p>
      <div class="row input-sm">
        <div class="col-xs-2"></div>
        <div class="col-xs-8">
          <div>
            <h4 class="text-success text-center"><strong>ISALU HOSPITAL LIMITED, ABUJA</strong></h4>

            <h6 class=" text-center text-success">COUNCIL MEMBERS SALARY ANALYSIS FOR {{$month}} {{$year}}</h6>
          </div>
        </div>
        <div class="col-xs-2"></div>
      </div>
    </p>
  </div>

  <div >&nbsp;


<br/>
<br/>
        <div align="left">

        </div>

 </div>
 </div>
 <div style="background: url(../Images/watermark2.jpg) no-repeat 50% 30% !important; -webkit-print-color-adjust: exact;">

 </div>
 </div>
 <div class="col-md-12">
            <table class="table table-responsive table-bordered" id="tableData">
                  <tr class="tblborder">
                    <td class="tblborder"><div align="center"><strong>S/N</strong></div></td>
                    <td class="tblborder"><strong>BANK </strong></td>
                    <td class="tblborder"><strong>P.V.NO.</strong></td>
                    <td class="tblborder"><div align="center"><strong>NO OF STAFF</strong></div></td>
                    <td class="tblborder"><div align="center"><strong>BASIC (CONSOLIDATED)</strong> (&#8358;)</div></td>
                    <td class="tblborder"><strong>ALLOWANCE</strong></td>
                    <td class="tblborder"><strong>GROSS PAY</strong></td>
                    <td class="tblborder"><strong>LESS PAYE</strong></td>
                    <td class="tblborder"><strong>NET PAY</strong></td>
                  </tr>
                  <?php $counter=session('serialNo');
                  $sum =0;?>
                  <?php
                  $subTotal=0;
                  $bkID ='';
                  $bcounter=0;
                  $refstaff='';
                  $k=1;
                  $totalBasic = 0;
                  $totalEarn =0;
                  $totalTax =0;
                  $totalNepay  = 0;
                  $totalStaff =0;
                  $totalAllow =0;

                  ?>
                  @foreach ($analysis as $reports)
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
                  @php

                  $furPaye = DB::table('tblotherEarningDeduction')->where('CVID','=',24)->where('staffid','=',$reports->staffid)->where('year','=', $reports->year)->where('month','=', $reports->month)->first();

                  if($furPaye != '')
                  {
                  $fpaye = $furPaye->amount;
                  }
                  else
                  {
                   $fpaye = 0;
                  }


                  @endphp
                  <tr>
                  <td>{{$k++}}</td>
                  <td> {{ $reports->bankname }} </td>
                  <td></td>
                  <td > <?php $totalStaff += 1; ?> 1</td>
                  <td align="right"><?php $totalBasic += $reports->Bs; ?>{{ number_format( $reports->Bs, 2, '.', ',')}}</td>
                  <td align="right"><?php $totalAllow += $reports->PEC + $ser + $funiture; ?>{{ number_format( $reports->PEC + $ser + $funiture, 2, '.', ',')}}</td>
                  <td align="right"><?php $totalEarn += $reports->TEarn; ?>{{ number_format( $reports->TEarn, 2, '.', ',')}}</td>
                  <td align="right"><?php $totalTax += $reports->TAX + $fpaye; ?>{{ number_format( $reports->TAX + $fpaye, 2, '.', ',')}}</td>
                  <td align="right"><?php $totalNepay += $reports->NetPay; ?>{{ number_format( $reports->NetPay, 2, '.', ',')}}</td>
                  </tr>

                  @endforeach

             <tr>

                  <td colspan="3"><strong>TOTAL</strong></td>
                  <td > <strong>{{$totalStaff}}</strong></td>
                  <td align="right"><strong>{{ number_format($totalBasic, 2, '.', ',')}} </strong></td>
                  <td align="right"><strong>{{ number_format($totalAllow, 2, '.', ',')}} </strong></td>
                  <td align="right"><strong>{{ number_format( $totalEarn, 2, '.', ',')}} </strong></td>
                  <td align="right"><strong>{{ number_format( $totalTax, 2, '.', ',')}} </strong></td>
                  <td align="right"><strong>{{ number_format( $totalNepay, 2, '.', ',')}} </strong></td>
                  </tr>


</table>

</div>
<div class="col-md-12">
<table width="1100" border="1" class="tables sign" style="margin-bottom:20px;">

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
</div>

<script src="{{asset('assets/js/jQuery-2.2.0.min.js')}}"></script>


<script src="{{asset('assets/js/table2excel.js')}}"></script>

@if($lock > 0 )

@else

<script type="text/javascript">

  $('body').bind('copy paste',function(e) {
    e.preventDefault(); return false;
});
</script>

@endif

<script>


 var $btnDLtoExcel = $('#DLtoExcel-2');
    $btnDLtoExcel.on('click', function () {
        $("#tableData").excelexportjs({
            containerid: "tableData"
            , datatype: 'table'
        });

    });

</script>

<script type="text/javascript">
        function Export() {
            $("#tableData").table2excel({
                filename: "{{session('month')}}_{{session('year')}}_Mandate.xls"
            });
        }
    </script>




  <script> var murl = "{{ url('/')}}"; </script>

<script type="text/javascript">
$( function() {

  $(".selectname").on('change', function(){

var id = $(this).val();
//alert(id);
$token = $("input[name='_token']").val();
$.ajax({
  headers: {'X-CSRF-TOKEN': $token},
  url: murl +'/epay/test',
  type: "post",
  data: {'signid': id},

  success: function(datas){
  console.log(datas.phoneno);
    //alert(datas.phoneno);
   $('.sign1').html(datas.phoneno);

  }
});
});


  $(".selectname2").on('change', function(){

var id = $(this).val();
//alert(id);
$token = $("input[name='_token']").val();
$.ajax({
  headers: {'X-CSRF-TOKEN': $token},
  url: murl +'/epay/test',
  type: "post",
  data: {'signid': id},

  success: function(datas){
  console.log(datas.phoneno);
    //alert(datas.phoneno);
   $('.sign2').html(datas.phoneno);

  }
});
});

$(".selectname3").on('change', function(){

var id = $(this).val();
//alert(id);
$token = $("input[name='_token']").val();
$.ajax({
  headers: {'X-CSRF-TOKEN': $token},
  url: murl +'/epay/test',
  type: "post",
  data: {'signid': id},

  success: function(datas){
  console.log(datas.phoneno);
    //alert(datas.phoneno);
   $('.sign3').html(datas.phoneno);

  }
});
});


});
</script>

</body>
</html>
