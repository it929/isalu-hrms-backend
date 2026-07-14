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


@if($lock > 0)

@else

<style type="text/css" media="print">
   body
    {
        display: none;
        visibility: hidden;
    }
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
            <h4 class="text-success text-center"><strong>ISALU HOSPITAL LIMITED</strong></h4>
            <h5 class="text-center text-success"><strong> ISALU HOSPITAL LIMITED COMPLEX </strong></h5>
            <h6 class=" text-center text-success"><strong>THREE ARMS ZONE, CENTRAL DISTRICT PMB 308, ABUJA</strong></h6>
            <h6 class=" text-center text-success"><strong>ACCOUNT NUMBER: 1015498475</strong></h6>
            <h6 class=" text-center text-success">E-PAYMENT SCHEDULE</h6>
          </div>
        </div>
        <div class="col-xs-2"></div>
      </div>
    </p>
  </div>

  <div >&nbsp;

    <p>
      <div class="row">
        <div align="left" class="col-xs-6" style="padding-top:90px;padding-left:40px;">
          <table >
            <tr><td align="left">THE BRANCH MANAGER,</td></tr>
            <tr><td align="left">ZENITH BANK PLC</td></tr>
            <tr><td align="left">MAITAMA, ABUJA</td></tr>

          </table>
        </div>

        <div align="right" class="col-xs-6" style="padding-top:90px; padding-right:40px;">
          <table >
            <tr><td><div align="left">{{date('d/m/Y')}} <br />
            SCN/9/19/04/VOL.XII/152 <br/>
            SCN/ZENITH/{{$nmonth = date("m", strtotime($month))}}/BAT-@if(count($bat) > 0) {{$bat->batNo}} @else @endif  <br/>



            <br />
          </div></td></tr>
          </table>
        </div>
      </div>
    </p>

      <br />
        <div align="left">

        </div>

 </div>
 </div>
 <div style="background: url(../Images/watermark2.jpg) no-repeat 50% 30% !important; -webkit-print-color-adjust: exact;">
 <?php

 $sig1 = DB::table('tblmandatesignatory')->join('tblmandatesignatoryprofiles','tblmandatesignatoryprofiles.id','=','tblmandatesignatory.signatoryID')->where('tblmandatesignatory.id','=',1)->first();
 $sig2 = DB::table('tblmandatesignatory')->join('tblmandatesignatoryprofiles','tblmandatesignatoryprofiles.id','=','tblmandatesignatory.signatoryID')->where('tblmandatesignatory.id','=',2)->first();
 $sum1 = 0;
 $t=0;
 ?>
 @foreach($epayment_detail as $list)

  <?php
                  $sum1 = $sum1 +$list->NetPay;
                  if($nhisexist == 0)
                    {
                        $t = $refundSum + $nhisNew + $taxSum + $coopSumSaving + $coopSumLoan + $unionSum + $totalPaySum;

                    }
                    elseif($nhisexist > 0 && $nhisbal->purpose == 'Over Pay')
                    {
                       $t = $refundSum + $nhisNew + $taxSum + $coopSumSaving + $coopSumLoan + $unionSum + $totalPaySum - $nhisbal->amount;
                    }
                    elseif($nhisexist > 0 && $nhisbal->purpose == 'Short Pay')
                    {
                       $t = $refundSum + $nhisNew + $taxSum + $coopSumSaving + $coopSumLoan + $unionSum + $totalPaySum + $nhisbal->amount;
                    }
                  //$t = $refundSum + $nhis + $taxSum + $coopSumSaving + $coopSumLoan + $unionSum + $totalPaySum;
                  //$tots  = $sum1 + $t;
                  ?>

 @endforeach


 <?php
                    $finalsum=0;
                    ?>
                    @foreach ($epayment_total as $reports)
                    <?php
                    $finalsum = $finalsum + $reports->NetPay;
                    ?>

                    @endforeach
                    <div style="width:100%;float:left;">
 <div class="" style="width:80%; margin:10px auto">
 Please credit the account(s) of the under listed beneficiary(s) and debit our account above with: (&#8358;)<b>{{ number_format( $t, 2, '.', ',')}}</b><br>
                          <span id="result">
                            <script type="text/javascript">
                              var amount = "";
                              var amount = "<?php echo number_format($t, 2, '.', '') ; ?>";
                              var money = amount.split('.');
                              function lookup()
                              {
                                var words;
                                var naira = money[0];
                                var kobo = money[1];
                                var word1 = toWords(naira)+" naira";
                                var word2 = ", "+toWords(kobo)+" kobo";
                                if(kobo != "00")
                                  words = word1 + word2;
                                else
                                  words = word1;
                                document.getElementById('result').innerHTML = words.toUpperCase();
                              }
                            </script>

 </div>
 </div>
<table border="0" align="center" cellpadding="0" cellspacing="0" >
        <tr>
          <td colspan="2">
            <table class="table table-responsive table-bordered" id="tableData">
                  <tr class="tblborder">
                    <td class="tblborder"><div align="center"><strong>S/N</strong></div></td>
                    <td class="tblborder"><div align="center"><strong>BENEFICIARY</strong></div><div align="center"></div>            <div align="center"></div></td>
                    <td class="tblborder"><strong>BANK </strong></td>
                    <td class="tblborder"><strong>BRANCH</strong></td>
                    <td class="tblborder"><div align="center"><strong>ACC NUMBER</strong></div></td>
                    <td class="tblborder"><div align="center"><strong>AMOUNT</strong> (&#8358;)</div></td>
                    <td class="tblborder"><strong>S/CODE</strong></td>
                    <td class="tblborder"><strong>PURPOSE OF PAYMENT</strong></td>
                  </tr>
                  <?php $counter=session('serialNo');
                  $sum =0;?>
                  <?php
                  $subTotal=0;
                  $bkID ='';
                  $bcounter=0;
                  $refstaff='';
                  ?>
                  @foreach ($epayment_detail as $reports)

                  @if($bkID != $reports->bankID&& $bkID !=''  )
                  @if($bkID=='6' || $bkID=='33')
                  <tr class="tblborder">
                  <td class="tblborder"> {{$counter}}</td>
                    <td  class="tblborder"><strong> @if($bkID=='6')  	ASO BANK @elseif($bkID=='33')  Garki Micro Finance Bank @else @endif </strong> </td>
                    <td class="tblborder" colspan="2"> @if($bkID=='6') UNITY BANK @elseif($bkID=='33')  UNITY BANK @else  @endif  </td>
                    <td class="tblborder totext" > &nbsp;@if($bkID=='6') 0018068474 @elseif($bkID=='33')  0001478903 @else @endif
                    <td colspan="1" class="tblborder"><strong> {{ number_format($subTotal,2) }}  </strong></td>
                    <td class="tblborder" colspan=""> </td>
                    <td class="tblborder" colspan=""> @if($bkID=='6') {{$refstaff}} and {{$bcounter-1}} others {{session('month')}}  {{session('year') }} Staff Salary  @elseif($bkID=='33')  {{$refstaff}} and {{$bcounter-1}} others {{session('month')}}  {{session('year') }} Staff Salary @else  @endif  </td>
                  </tr>
                   <?php $counter=$counter+1;   ?>
                  @else
                   <tr class="tblborder">
                    <td colspan="2" class="tblborder"><strong> Sub Total: </strong> </td>
                    <td class="tblborder" colspan="2"> @if($bkID=='6') UNITY BANK @elseif($bkID=='33')  UNITY BANK @else  @endif  </td>
                    <td class="tblborder totext" >&nbsp; @if($bkID=='6') 0018068474 @elseif($bkID=='33')  0001478903 @else @endif
                    <td colspan="1" class="tblborder"><strong> {{ number_format($subTotal,2) }}  </strong></td>
                    <td class="tblborder" colspan=""> </td>
                    <td class="tblborder" colspan=""> @if($bkID=='6') {{$refstaff}} and {{$bcounter-1}} others {{session('month')}}  {{session('year') }} Staff Salary  @elseif($bkID=='33')  {{$refstaff}} and {{$bcounter-1}} others {{session('month')}}  {{session('year') }} Staff Salary @else  @endif  </td>
                  </tr>
                  @endif

                 <?php
                  $subTotal=0;
                  $bcounter=0;
                  $refstaff=$reports->name;
                  ?>
                  @endif
                  <?php
                  $bkID = $reports->bankID;

                  $subTotal+=$reports->NetPay;
                  ?>
                  @if($bkID=='6') @elseif($bkID=='33') @else
                  <tr class="tblborder">
                    <td class="tblborder"> {{$counter}}</td>
                    <td class="tblborder"> {{ $reports->name }} </td>
                    <td class="tblborder"> {{ $reports->bank }}   </td>
                    <td class="tblborder"> {{ $reports->bank_branch }} </td>
                    <td class="tblborder totext"> &nbsp;{{ $reports->AccNo}}    </td>
                    <td class="tblborder" align="right">   {{ number_format( $reports->NetPay, 2, '.', ',')}} </td>
                    <td class="tblborder"> </td>
                    <td class="tblborder">  @if($reports->remarks!=null && $reports->month == session('month') && $reports->year == session('year') ) {{$reports->remarks}} @else {{session('month')}}  {{session('year') }} Staff Salary @endif </td>
                  </tr>
                   <?php $counter=$counter+1;   ?>
                  @endif
                  <?php
                  $sum = $sum +$reports->NetPay;

                  $bcounter=$bcounter+1;

                  ?>
                  @endforeach

                  <?php
                    $finalsum=0;
                    //$nhisAmount =0;
                    if($nhisexist == 0)
                    {
                        $nhisAmount = 0;

                    }
                    elseif($nhisexist > 0 && $nhisbal->purpose == 'Over Pay')
                    {
                        $nhisAmount = $nhisbal->amount;
                        $sign = "+";
                    }
                    elseif($nhisexist > 0 && $nhisbal->purpose == 'Short Pay')
                    {
                        $nhisAmount = $nhisbal->amount;
                        $sign = "-";
                    }

                    ?>
                    @foreach ($epayment_total as $reports)
                    <?php
                    $finalsum = $finalsum + $reports->NetPay;
                    ?>

                    @endforeach
                     @if($bkID !=''  )
                  <tr class="tblborder">

                    <td colspan="5" class="tblborder"><strong> Sub Total:</strong> </td>
                    <td colspan="3" class="tblborder"><strong> {{ number_format($subTotal,2) }}  </strong></td>
                  </tr>
                 <?php
                  $subTotal=0;
                  ?>
                  @endif
                  <tr class="tblborder">
                    <td class="tblborder" colspan="5"><strong>Total</strong></td>
                    <td class="tblborder" align="right"><strong> {{ number_format($sum, 2, '.', ',')}} </strong></td>
                    <td class="tblborder" colspan="2"></td>
                  </tr>
                  <tr>
                    <td class="tblborder"></td>
                    <td class="tblborder">2009822140</td>
                   <td class="tblborder" colspan =2>JUDICIAL STAFF UNION OF NIGERIA</td>

                    <td class="tblborder">FIRST BANK</td>
                    <td class="tblborder">{{number_format($unionSum,2)}}</td>
                     <td class="tblborder"></td>
                    <td class="tblborder">{{session('month')}}  {{session('year')}}  DUES</td>

                  </tr>
                  <tr>
                    <td class="tblborder"></td>
                    <td class="tblborder">4287667010</td>
                    <td class="tblborder" colspan=2>SCN STAFF MULTIPURPOSE COOPERATIVE SOCIETY</td>

                    <td class="tblborder">FCMB</td>
                    <td class="tblborder">{{number_format($coopSumSaving + $coopSumLoan,2)}}</td>
                     <td class="tblborder"></td>
                    <td class="tblborder">{{session('month')}}  {{session('year')}}  DEDUCTION</td>

                  </tr>
                  <!--<tr>
                    <td class="tblborder"></td>
                    <td class="tblborder">300002181</td>
                    <td class="tblborder" colspan=2>FIRS</td>
                    <td class="tblborder">CBN</td>
                    <td class="tblborder">{{number_format($taxSum,2)}}</td>
                     <td class="tblborder"></td>
                    <td class="tblborder">{{session('month')}}  {{session('year')}}  DEDUCTION</td>
                  </tr>-->
                  @foreach($taxPayee as $list)
                  @php
                  $tax = DB::table('tblpayment_consolidated')->where('year','=',$year)->where('month','=',$month)->where('current_state','=',$list->id)->where('rank','!=',2)->sum('TAX');
                  @endphp
                  <tr>
                    <td class="tblborder"></td>
                    <td class="tblborder">{{$list->account_no}}</td>
                    <td class="tblborder" colspan=2>{{$list->address}}</td>
                    <td class="tblborder">{{$list->bankname}}</td>
                    <td class="tblborder">{{number_format($tax,2)}}</td>
                     <td class="tblborder"></td>
                    <td class="tblborder">{{session('month')}}  {{session('year')}}  DEDUCTION</td>
                  </tr>
                  @endforeach
                  <tr>
                    <td class="tblborder"></td>
                    <td class="tblborder">0020163661013</td>
                    <td class="tblborder" colspan=2>NHIS</td>
                    <td class="tblborder">CBN</td>

                    <td class="tblborder">
                        <!-- @if($nhisexist == 0)
                         {{number_format($nhis,2)}}
                         @else
                         @if($nhisexist > 0 && $nhisbal->purpose == 'Short Pay')
                         {{number_format($nhis + $nhisbal->amount,2)}}
                         @elseif($nhisexist > 0 && $nhisbal->purpose == 'Over Pay')
                         {{number_format($nhis - $nhisbal->amount,2)}}
                         @endif

                         @endif-->
                         {{number_format($nhisNew,2)}}

                         </td>
                     <td class="tblborder"></td>
                    <td class="tblborder">{{session('month')}}  {{session('year')}}  DEDUCTION</td>

                  </tr>
                  <tr>
                    <td class="tblborder"></td>
                    <td class="tblborder">3000055905</td>
                    <td class="tblborder" colspan=2>FEDERAL GOVERNMENT HOUSING LOAN</td>

                    <td class="tblborder">CBN</td>
                    <td class="tblborder">{{number_format($refundSum,2)}}</td>
                    <td class="tblborder"></td>
                    <td class="tblborder">{{session('month')}}  {{session('year')}}  LOAN REMITTANCE FOR ADESOJI OYE</td>

                  </tr>

                  <tr>
                    <td colspan ="5"class="tblborder"><strong>SUB TOTAL</></td>

                    <td colspan ="3" class="tblborder">
                        @if($nhisexist == 0)
                        <strong> {{number_format($refundSum + $nhisNew + $taxSum + $coopSumSaving + $coopSumLoan + $unionSum,2)}}</strong
                        @else
                         @if($nhisexist > 0 && $nhisbal->purpose == 'Short Pay')
                        <strong> {{number_format($refundSum + $nhisNew + $taxSum + $coopSumSaving + $coopSumLoan + $unionSum + $nhisbal->amount,2)}}</strong
                         @elseif($nhisexist > 0 && $nhisbal->purpose == 'Over Pay')
                         <strong> {{number_format($refundSum + $nhisNew + $taxSum + $coopSumSaving + $coopSumLoan + $unionSum - $nhisbal->amount,2)}}</strong
                         @endif
                         @endif
                        </td>

                  </tr>
                  <tr>
                    <td colspan ="5"class="tblborder"><strong>GRAND TOTAL</></td>

                    <td colspan ="3" class="tblborder">
                          @if($nhisexist == 0)
                        <strong>{{number_format($refundSum + $nhisNew + $taxSum + $coopSumSaving + $coopSumLoan + $unionSum + $sum ,2)}} </strong
                        @else
                         @if($nhisexist > 0 && $nhisbal->purpose == 'Short Pay')
                         <strong>{{number_format($refundSum + $nhisNew + $taxSum + $coopSumSaving + $coopSumLoan + $unionSum + $sum + $nhisbal->amount ,2)}} </strong
                         @elseif($nhisexist > 0 && $nhisbal->purpose == 'Over Pay')
                         <strong>{{number_format($refundSum + $nhisNew + $taxSum + $coopSumSaving + $coopSumLoan + $unionSum + $sum - $nhisbal->amount ,2)}} </strong
                        @endif
                         @endif
                        </td>

                  </tr>
                </table>
          </td>
        </tr>



        <tr><td colspan="2">
          <div class="no-print" align="center">
                   <input type="button" class="hidden-print" id="btnExport" value="Export to Excel" onclick="Export()" />
                    </div>

        </td></tr>
        <tr>
          <td colspan="2">
          <table class="table">

           <tr>
            <td style="width: 35%">
           <div class="col-md-12  sigtab" style="padding:0px;">
            <div class="inner-wrap">
                    <p><strong> Authorised Signature </strong></p>
                    <p>Name: {{$sig1->Name }}</p>
                   <p>Signature & Thumb Print <br><br><br><br><br><br></p>
                   <p>Date: </p>
                   <p>Phone No. {{$sig1->phone }}</p>
            </div>

             <div class="inner-wrap">
                    <p><strong> Authorised Signature </strong></p>
                    <p>Name: {{$sig2->Name }}</p>
                   <p>Signature & Thumb Print <br><br><br><br><br><br></p>
                   <p>Date: </p>
                   <p>Phone No. {{$sig2->phone}}</p>
            </div>

           </div>
        </td>

        <td style="width: 30%">

        </td>


          <td style="width: 35%">
          <div class="col-md-12 col-xs-12 col-sm-12 sigtab" style="padding:0px;">
              <div class="inner-wrap">
                    <p><strong>  </strong></p>
                    <p>Name: <br><br></p>
                   <p>Signature & Thumb Print <br><br><br><br><br><br></p>
                   <p>Date: <br><br><br></p>


              </div>

              <div class="inner-wrap">
                    <p><strong> Confirm By Me  </strong></p>
                    <p>Name: </p>
                   <p>Signature & Thumb Print <br><br><br><br><br><br></p>
                   <p>Date: <br><br><br></p>

              </div>
          </div>
        </td>

         </table>
          </td>

        </tr>
        <tr>
          <td colspan="2"> <h2><a  class= "no-print" type="submit" class="btn btn-success btn-sm pull-right" href = "{{ url('/epayment') }}">Back</a></h2></td>
          <!--<button id='DLtoExcel-2'  class="btn btn-success hidden-print">Export to Excel</button>-->

        </tr>

</table>

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
