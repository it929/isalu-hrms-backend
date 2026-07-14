<!DOCTYPE html>
<html>
<head>

  <title>ISALU HOSPITAL LIMITED...::...Bank Schedule</title>
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
table tr th, table tr td
{
    font-size:22px;
    border:1px solid #333 !important;
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
        <div class="col-xs-2"></div>
        <div class="col-xs-8">
          <div>
            <h2 class="text-center"><strong>ISALU HOSPITAL LIMITED, ABUJA</strong></h2>

            <h3 class=" text-center">COUNCIL MEMBERS SALARY SCHEDULE,<br/> FOR THE MONTH OF {{$month}} {{$year}}</h3>
          </div>
        </div>
        <div class="col-xs-2"></div>
      </div>
    </p>
  </div>

  <div >&nbsp;



      <br />
        <div align="left">

        </div>

 </div>
 </div>
 <div style="background: url(../Images/watermark2.jpg) no-repeat 50% 30% !important; -webkit-print-color-adjust: exact;">
 <?php
 $sum1 = 0;
 ?>
 <div style='width:80%; margin:auto;'>

            <table class="table table-responsive table-bordered">
                  <tr>
                    <td><div align="center"><strong>S/N</strong></div></td>
                    <td><div align="center"><strong>BENEFICIARY</strong></div><div align="center"></div>            <div align="center"></div></td>
                    <td><div align="center"><strong>NETPAY</strong> (&#8358;)</div></td>
                  </tr>
                  <?php  $counter=1;
                  $sum =0;?>
                  <?php
                  $subTotal=0;
                  $bkID ='';
                  ?>
                  @foreach ($epayment_detail as $reports)


                  <tr>
                    <td> {{$counter++}}</td>
                    <td>{{$reports->council_title}} {{$reports->surname }} {{$reports->first_name }} {{$reports->othernames }}</td>
                    <td align="right"><?php $sum += $reports->NetPay; ?>   {{ number_format( $reports->NetPay, 2, '.', ',')}} </td>






                  </tr>
                  @endforeach

                  <tr>
                    <td colspan="2"><strong>Total</strong></td>
                    <td align="right"><strong> {{ number_format($sum, 2, '.', ',')}} </strong></td>


                  </tr>
                </table>

</div>

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
