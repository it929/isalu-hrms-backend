<html>
<head>
  <meta charset="utf-8">
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <title>Details of Service</title>
  <!-- Tell the browser to be responsive to screen width -->
  <meta content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no" name="viewport">
  <link rel="stylesheet" href="{{ asset('assets/css/bootstrap.min.css') }}">
  <link rel="stylesheet" href="{{ asset('assets/font-awesome/css/font-awesome.min.css') }}">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/ionicons/2.0.1/css/ionicons.min.css">
  <!--Number to word-->
  <script type="text/javascript" src="{{ asset('assets/js/number_to_word.js') }}"></script>

</head>
<body>

  <div class="container">
  <p>
	<div class="row">
		<div class="col-xs-2"><img src="{{asset('Images/logo.jpg')}}" class="responsive"></div>
		<div class="col-xs-8">
			<div>
				<h4 class="text-success text-center"><strong>ISALU HOSPITAL LIMITED</strong></h4>
				<h5 class="text-center text-success"><strong>10, PORTHARCOURT CRESCENT, AREA 11, GARKI, ABUJA</strong></h5>
				<h6 class=" text-center text-success"><strong>REPORT ON DETAILS OF SERVICE IN THE FORCES</strong></h6>
			</div>
		</div>
		<div class="col-xs-2"><img src="{{asset('Images/coat.jpg')}}" class="responsive"></div>
	</div>
	</p>

	<div>
		<p>
			<div class="row">
				<div align="left" class="col-xs-6">
					<table >
						<tr><td align="left">FILE NO.: {{$staffFullDetails->fileNo}}</td></tr>
						<tr><td align="left">DIVISION: {{$staffFullDetails->division}}</td></tr>
						<tr><td align="left">DESIGNATION: {{$staffFullDetails->Designation}}</td></tr>
					</table>
				</div>
				<div align="right" class="col-xs-6">
					<table >
						<tr><td align="right">SECTION: {{$staffFullDetails->section}}</td></tr>
						<tr><td align="right">APPOINTMENT DATE: {{date( 'D M, Y', strtotime($staffFullDetails->appointment_date))}}</td></tr>
						<tr><td align="right">PRINTED ON: {{date('d-M-Y')}}</td></tr>
					</table>
				</div>
			</div>
		</p>

			<hr />

			<div class="row">
				<div class="col-sm-12">

                    @php if($staffFullDetailsDetailsService != null){ @endphp
                    @foreach($staffFullDetailsDetailsService as $ds)
                    <table class="table table-condensed table-striped table-bordered">
                     <tbody class="text-gray">
                        <tr>
                          <td width="250">
                            Arm of Service
                          </td>
                          <td>{{$ds->armOfservice}}</td>
                        </tr>
                        <tr>
                           <td>
                              Service No.
                            </td>
                            <td>{{$ds->serviceNumber}}</td>
                        </tr>
                        <tr>
                            <td>
                              Last Unit
                            </td>
                            <td>{{$ds->lastUnit}}</td>
                        </tr>
                        <tr>
                           <td>
                              Reason for Leaving
                           </td>
                           <td>{{$ds->reasonForLeaving}}</td>
                        </tr>
                      </tbody>
                    </table> <br />
                    @endforeach
                    @php } @endphp

				</div>
			</div>
		</div>

	<div align="center" class="hidden-print">
		<a href="{{url('/profile/details/'.$staffFullDetails->fileNo)}}" title="Back" class="btn btn-success"> Go Back</a>
	</div>
	<br />

</div>
</body>
</html>
