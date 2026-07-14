<!DOCTYPE html>
<html>
<head>

  <title>ISALU HOSPITAL LIMITED</title>
  <link rel="stylesheet" href="{{ asset('assets/css/bootstrap.min.css') }}">


<script type="text/javascript" src="{{ asset('assets/js/number_to_word.js') }}">
</script>
</head>
<body style="" onload="lookup()">

<div class="col-md-12">
<div class="col-md-12">
<div>
      <p>
      <div class="row input-sm">
        <div class="col-xs-2"> <img src="{{asset('Images/NSITF.jpg')}}" class="img-responsive responsive"> </div>
        <div class="col-xs-8">
          <div>
            <h5>FEDERAL REPUBLIC OF NIGERIA</h5>
            <h4 class="text-success text-center"><strong>NIGERIA SOCIAL INSURANCE TRUST FUNDS(NSITF)</strong></h4>
            <h5 class="text-center text-success"><strong>Employees Compensation Act, 2010 </strong></h5>
            <h5 class="text-center text-success"><strong>FOR {{$month}} {{$year}} </strong></h5>
            <h5 class=" text-center text-success"><strong>Employer Schedule of Payments(Budget)</strong></h5>
            <h6 class=" text-center text-success"> (Section 40(1)(a) of Act) </h6>
          </div>
        </div>
        <!--<div class="col-xs-2"><img src="{{asset('Images/njc-logo.jpg')}}" class="img-responsive responsive"></div>-->
      </div>
    </p>
  </div>

  <div >&nbsp;

    <p>
      <div class="row">
        <div align="left" class="col-xs-6">

        </div>

        <div align="right" class="col-xs-6">
          <table >
            <tr><td><div align="left">ECS.RE03<br />

          </div></td></tr>
          </table>
        </div>
      </div>
    </p>



 </div>
 </div>
 <div style="">
  <div style=""><div class="col-sm-6"><strong>Employer Name:</strong> ISALU HOSPITAL LIMITED</div> <div class="col-sm-6"> <strong>Employer NSITF No.:</strong> </div></div>
  </div>

<table border="0" align="center" cellpadding="0" cellspacing="0" >
        <tr>
          <td colspan="2">
            <table class="table table-responsive table-bordered">
                  <tr class="tblborder">
                    <td class="tblborder" rowspan="2" align="center" valign="middle"><div align="center"><strong>S/N</strong></div></td>
                    <td class="tblborder" rowspan="2" align="center" valign="middle"><div align="center"><strong>Surname</strong></div><div align="center"></div>            <div align="center"></div></td>
                    <td class="tblborder" rowspan="2" align="center" valign="middle"><strong>First Name </strong></td>
                    <td class="tblborder" rowspan="2" align="center" valign="middle"><strong>Middle Name</strong></td>
                    <td class="tblborder" rowspan="2" align="center" valign="middle"><strong>Gender</strong></td>
                    <td class="tblborder" rowspan="2" align="center" valign="middle"><strong>Employee<br/> Staff ID No.</strong></td>
                    <td class="tblborder" rowspan="2" align="center" valign="middle"><div align="center"><strong>Employee <br/> Date of Employment</strong></div></td>
                    <td class="tblborder" rowspan="2" align="center" valign="middle"><div align="center"><strong> Date of Birth</strong></div></td>
                    <td class="tblborder" rowspan="2" align="center" valign="middle"><div align="center"><strong>State of Origin</strong></div></td>
                    <td class="tblborder" rowspan="2" align="center" valign="middle"><div align="center"><strong>LGA</strong></div></td>
                    <td class="tblborder" rowspan="2" align="center" valign="middle"><div align="center"><strong>Contact Phone <br/> Number</strong></div></td>
                    <td class="tblborder" rowspan="2" align="center" valign="middle"><div align="center"><strong>Alternate Phone No.</strong></div></td>
                    <td class="tblborder" rowspan="2" align="center" valign="middle"><div align="center"><strong>Job Title</strong></div></td>


                    <td class="tblborder" colspan="2" align="center" valign="top"><strong>Staff Monthly</strong></td>
                  </tr>
                  <tr>

                    <td class="tblborder" colspan="2" width="52" align="center" valign="middle"><strong>Net</strong></td>

                  </tr>
                  <?php $counter=1;

                  $totalBasic = 0;
                  $totalPercent = 0;
                  $sum =0;?>
                  @foreach ($staff as $reports)
                  @php
                  $mth = array("JANUARY", "FEBRUARY", "MARCH", "APRIL", "MAY", "JUNE", "JULY", "AUGUST");

                  if($year > 2019)
                  {

                  if($year == 2020 && $month == 'JANUARY')
                  {
                   $per = $reports->Bs;
                  }
                  else if($year == 2020 && $month == 'FEBRUARY')
                  {
                  $per = $reports->Bs;
                  }
                  else if($year == 2020 && $month == 'MARCH')
                  {
                  $oneper = (1/100) * $reports->SOT;
                  $leftBal = $reports->SOT - $oneper;
                  $per = ($reports->NetPay - $leftBal);
                  }
                  else if($year == 2020 && $month == 'AUGUST')
                  {
                  $per = $reports->Bs;
                  }
                  else if($year ==2020 && !in_array($month, $mth))
                  {
                   $per = $reports->gross - $reports->PEC;
                  }
                  else
                  {
                   $per = $reports->NetPay;
                  }

                  }
                  else
                  {
                  $per = (1/100) * ($reports->Bs + $reports->AEarn);
                  }
                  //$gross = $reports->Bs + $reports->AEarn;
                  @endphp
                  <tr class="tblborder">
                    <td class="tblborder"> {{$counter++}}</td>
                    <td class="tblborder"> {{ $reports->surname }} </td>
                    <td class="tblborder"> {{ $reports->first_name }}</td>
                    <td class="tblborder"> {{ $reports->othernames }}</td>
                    <td class="tblborder" align="right">   {{ $reports->gender }} </td>
                    <td class="tblborder"> {{ $reports->fileNo }} </td>
                    <td class="tblborder"> {{ $reports->appointment_date}} </td>
                    <td class="tblborder"> {{ $reports->dob}} </td>
                    <td class="tblborder" align="right">   {{ $reports->State }} </td>
                    <td class="tblborder" align="right">   {{ $reports->lga }} </td>
                    <td class="tblborder" align="right">   {{ $reports->phone }} </td>
                    <td class="tblborder" align="right">   {{ $reports->alternate_phone }} </td>
                    <td class="tblborder">   {{ $reports->designation }} </td>

                    <td class="tblborder" align="right"> <?php $totalPercent +=$per; ?> {{number_format($per,2)}}  </td>

                  @endforeach
                  <tr class="tblborder">

                    <td class="tblborder" colspan="13"><strong>Total Amount</strong></td>

                    <td class="tblborder" align="right"><strong> {{ number_format($totalPercent,2)}} </strong></td>

                  </tr>

                   <tr class="tblborder">

                    <td class="tblborder" colspan="13"><strong>1(one) Percent of Total Net Emolument</strong></td>


                    <td class="tblborder" align="right">
                        @php
                        $onePercentTotal = (1/100) * $totalPercent;

                        @endphp
                        <strong> {{ number_format($onePercentTotal, 2)}} </strong></td>

                  </tr>
                </table>
          </td>
        </tr>

        <tr><td colspan="2">
          <div align="left">

            <p> Authorized Signatory____________________________________________ Name of Officer___________________________ Position ____________________________ Date________________</p>

            <p>____________________________________________________________________________________________________________________</p>
            <p class="text-center">Tel: +234-9-2911811; email: info@nsitf.net; website: www.nsitf.net</p>
          </div>

        </td></tr>
        <tr>
          <td colspan="2">

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
