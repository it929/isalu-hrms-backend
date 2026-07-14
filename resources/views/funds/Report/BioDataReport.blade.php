<html>
<head>
  <meta charset="utf-8">
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <title>Bio-Data</title>
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
				<h6 class=" text-center text-success"><strong>REPORT ON STAFF BIO-DATA</strong></h6>
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
						<tr><td align="right">PRINTED ON: {{date('D-M-Y')}}</td></tr>
					</table>
				</div>
			</div>
		</p>

			<hr />

			<div class="row">
				<div class="col-sm-12">
					<table class="table table-responsive table-condensed table-striped table-bordered" 
					style="/* for IE */filter:alpha(opacity=80);/* CSS3 standard */opacity:0.8;"> 
					<tbody>
						<tr>
							<td width="300">SURNAME</td>
							<td align="left">{{$staffFullDetails->surname}} </td>
						</tr>
						<tr>
							<td width="300">FIRST NAME</td>
							<td align="left">{{$staffFullDetails->first_name}} </td>
						</tr>
						<tr>
							<td width="300">OTHER NAME</td>
							<td align="left">{{$staffFullDetails->othernames}} </td>
						</tr>
						<tr>
							<td width="300">GENDER</td>
							<td align="left">{{$staffFullDetails->gender}} </td>
						</tr>
						<tr>
							<td width="300">HOME ADDRESS</td>
							<td align="left">{{$staffFullDetails->home_address}}</td>
						</tr>
						<tr>
							<td width="300">STATE</td>
							<td align="left">{{$staffFullDetails->current_state}}</td>
						</tr>
						<tr>
							<td width="300">NATIONALITY</td>
							<td align="left">{{$staffFullDetails->nationality}}</td>
						</tr>
						<tr>
							<td width="300">PHONE</td>
							<td align="left">{{$staffFullDetails->phone}}</td>
						</tr>

						<tr>
							<td width="300">DATE OF BIRTH</td>
							<td align="left">
								 @php if((($staffFullDetails->dob) == "0000-00-00") or (($staffFullDetails->dob) == "")){ @endphp
			                      {{$staffFullDetails->dob}}
			                    @php }else{ @endphp
			                      {{date('D M, Y', strtotime($staffFullDetails->dob))}}
			                     @php } @endphp
							</td>
						</tr>
						<tr>
							<td width="300">PLACE OF BIRTH</td>
							<td align="left">{{$staffFullDetails->placeofbirth}}</td>
						</tr>
						<tr>
							<td width="300">MARITAL STATUS</td>
							<td align="left">{{$staffFullDetails->maritalstatus}}</td>
						</tr>

						<tr>
							<td width="300">STAFF STATUS</td>
							<td align="left">{{$staffFullDetails->status_value}}</td>
						</tr>

					</tbody>
				</table>

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