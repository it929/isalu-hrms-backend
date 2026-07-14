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
</style>

<script type="text/javascript" src="{{ asset('assets/js/number_to_word.js') }}">
</script>
</head>
<body style="background: url(../Images/watermarks.jpg) repeat !important; -webkit-print-color-adjust: exact;" onload="lookup()">

<div class="col-md-12">
<div class="col-md-12">
<div>
      <p>
      <div class="row input-sm">
        <div class="col-xs-2"><img src="{{asset('Images/njc-logo.jpg')}}" class="img-responsive responsive" style="width:140px; height:120px;"></div>
        <div class="col-xs-8">
          <div>
            <h4 class="text-success text-center"><strong>ISALU HOSPITAL LIMITED</strong></h4>
            <!--<h5 class="text-center text-success"><strong>10, PORTHARCOURT CRESCENT, AREA 11, GARKI, ABUJA</strong></h5>
            <h6 class=" text-center text-success"><strong>ACCOUNT NUMBER: 2004656203</strong></h6>-->
            <h6 class=" text-center text-success">E-PAYMENT SCHEDULE</h6>
          </div>
        </div>
        <div class="col-xs-2"><img src="{{asset('Images/coat.jpg')}}" class="responsive"></div>
      </div>
    </p>
  </div>

  <div >&nbsp;

    <p>
      <div class="row">
        <div align="left" class="col-xs-6">
          <table >
            <tr><td align="left">The Branch Manager,</td></tr>
            <tr><td align="left">{{session('bank')}}</td></tr>
            <!--<tr><td align="left">Jos Street, Area 3,</td></tr>-->
            <!--<tr><td align="left">Garki, Abuja Nigeria.</td></tr>-->
          </table>
        </div>

        <div align="right" class="col-xs-6">
          <table >
            <tr><td><div align="left">Reference No:Per/{{date('Y')}}/1/A<br />
            Code:{{session('bankCode')}} <br/>
            Date Printed: {{date('d/m/Y')}} <br/>
            Division:


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
<table border="0" align="center" cellpadding="0" cellspacing="0">
        <tr>
          <td colspan="2">
            <table class="table table-responsive table-bordered">
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
                  @foreach ($epayment_detail as $reports)
                  <tr class="tblborder">
                    <td class="tblborder"> {{$counter}}</td>
                    <td class="tblborder"> {{ $reports->name }} </td>
                    <td class="tblborder"> {{ $reports->bank }} </td>
                    <td class="tblborder"> {{ $reports->bank_branch }} </td>
                    <td class="tblborder"> {{ $reports->AccNo}} </td>
                    <td class="tblborder" align="right">   {{ number_format( $reports->NetPay, 2, '.', ',')}} </td>
                    <td class="tblborder"> {{$reports->sort_code}}</td>
                    <td class="tblborder">  {{session('month')}}  {{session('year') }} Staff Salary  </td>
                  </tr>
                  <?php
                  $sum = $sum +$reports->NetPay;
                  $counter=$counter+1;
                  ?>
                  @endforeach
                  <tr class="tblborder">
                    <td class="tblborder" colspan="5"><strong>Total</strong></td>
                    <td class="tblborder" align="right"><strong> {{ number_format($sum, 2, '.', ',')}} </strong></td>
                    <td class="tblborder" colspan="2"></td>
                  </tr>
                </table>
          </td>
        </tr>
        <tr><td colspan="2">
          <div class="no-print" align="center">
                      //links
                    </div>
                    <?php
                    $finalsum=0;
                    ?>
                    @foreach ($epayment_total as $reports)
                    <?php
                    $finalsum = $finalsum +$reports->NetPay;
                    ?>
                    @endforeach
        </td></tr>
        <tr>
          <td colspan="2">
            <table border="0" cellpadding="0" cellspacing="0">
                        <tr>
                        <br>
                        @if (session('showTotal'))
                        <td class="no-border" colspan="5"><div align="center">
                          Please credit the account(s) of the above listed beneficiary(s) and debit our account above with: (&#8358;)<b>{{ number_format( $finalsum, 2, '.', ',')}}</b><br>
                          <span id="result">
                            <script type="text/javascript">
                              var amount = "";
                              var amount = "<?php echo number_format($finalsum, 2, '.', '') ; ?>";
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
                            <br/></span>
                          </div><br /></td>
                          @endif
                        </tr>
                        <tr>
                          <td class="no-border" align="left"><strong>ALL DUE PROCESS COMPLIED WITH</strong></td>
                        </tr>
                        <tr>
                          <td class="no-border" width="385"><div align="left"><strong>Authorized Signatory</strong><br />
                          </div></td>
                          <td class="no-border" width="167"><div align="left"><br />
                          </div></td>
                          <td class="no-border" width="1" rowspan="9">&nbsp;</td>
                          <td class="no-border" colspan="2"><div align="left"><strong>Submitted For Confirmation by</strong><br />
                          </div></td>
                        </tr>

                        <tr>
                          <td class="no-border" align="left">Name:  </td>
                          <td class="no-border" rowspan="2"><img src="{{asset('Images/sch.jpg')}}"   /></td>
                          <td class="no-border" align="left">Name: </td>
                          <td class="no-border" width="181" rowspan="2" align="left"><div align="left"><img src="{{asset('Images/sch.jpg')}}"  /></div></td>
                        </tr>
                        <tr>
                          <td class="no-border" align="left" valign="top">Signature: <br />
                            Date:</td>
                            <td class="no-border" width="448" align="left" valign="top">Signature:<br />
                              Date:</td>
                            </tr>
                            <tr>
                              <td class="no-border" align="left">Tel No: <span class="sign1"></span></td>
                              <td class="no-border">&nbsp;</td>
                              <td class="no-border" colspan="2" align="left">Tel No: <span class="sign2"></span></td>
                            </tr>
                            <tr>
                              <td class="no-border" align="left" width = 385><strong>Authorized Signatory</strong></td>
                              <td class="no-border">&nbsp;</td>
                              <td class="no-border" colspan="2"><div align="left"><strong>Confirmed Before Me</strong><br />
                              </div></td>
                            </tr>
                            <tr>
                              <td class="no-border" align="left">Name:
                          </td>
                              <td class="no-border" rowspan="2"><img src="{{asset('Images/sch.jpg')}}"   /></td>
                              <td  class="no-border" align="left">Name:</td>
                              <td class="no-border" rowspan="2" align="left"> <img src="{{asset('Images/sch.jpg')}}" /> </td>
                            </tr>
                            <tr>
                              <td class="no-border" valign="top" align="left">Signature:<br />
                                Date:</td>
                                <td class="no-border" valign="top" align="left">Signature:<br />
                                  Date:</td>
                                </tr>
                                <tr>
                                  <td class="no-border" align="left">Tel No: <span class="sign3"></span>
                                  </td>
                                  <td class="no-border">&nbsp;</td>
                                  <td class="no-border" colspan="2" align="left">Tel No:</td>
                                </tr>

                              </table>
          </td>
        </tr>
        <tr>
          <td colspan="2"> <h2><a  class= "no-print" type="submit" class="btn btn-success btn-sm pull-right" href = "{{ url('/epayment') }}">Back</a></h2></td>
        </tr>

</table>

<script src="{{asset('assets/js/jQuery-2.2.0.min.js')}}"></script>

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
