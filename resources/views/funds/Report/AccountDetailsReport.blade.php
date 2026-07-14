<html>
<head>
  <meta charset="utf-8">
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <title>Account Details</title>
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
				<h6 class=" text-center text-success"><strong>REPORT ON ACCOUNT DETAILS</strong></h6>
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
          <table class="table table-condensed table-striped table-bordered">
                  <tr>
                    <td width="250">
                       First Appointment
                    </td>
                    <td>
                        @php if((($staffFullDetails->appointment_date) == "0000-00-00") or (($staffFullDetails->appointment_date) == "")){ @endphp
                          {{$staffFullDetails->appointment_date}}
                        @php }else{ @endphp
                          {{date('D M, Y', strtotime($staffFullDetails->appointment_date))}}
                        @php } @endphp
                   </td>
                </tr>
                <tr>
                    <td>
                        First Arrival
                    </td>
                    <td>
                       @php if((($staffFullDetails->firstarrival_date) == "0000-00-00") or (($staffFullDetails->firstarrival_date) == "")){ @endphp
                          {{$staffFullDetails->firstarrival_date}}
                        @php }else{ @endphp
                          {{date('D M, Y', strtotime($staffFullDetails->firstarrival_date))}}
                        @php } @endphp
                    </td>
                </tr>
                <tr>
                    <td>
                      Employee Type
                    </td>
                    <td>
                      {{$staffFullDetails->employee_type}}
                    </td>
                </tr>
                <tr>
                    <td>
                       Designation
                    </td>
                    <td>
                      {{$staffFullDetails->Designation}}
                    </td>
                </tr>
                <tr>
                    <td>
                      Department
                    </td>
                    <td>
                      {{$staffFullDetails->department}}
                    </td>
                </tr>
                <tr>
                    <td>
                      section
                    </td>
                    <td>
                      {{$staffFullDetails->section}}
                    </td>
                </tr>
                <tr>
                    <td>
                      Grade
                    </td>
                    <td>
                      {{$staffFullDetails->grade}}
                    </td>
                </tr>
                <tr>
                    <td>
                      Step
                    </td>
                    <td>
                      {{$staffFullDetails->step}}
                    </td>
                </tr>
                <tr>
                    <td>
                      Bank
                    </td>
                    <td>
                      {{$staffFullDetails->bank}}
                    </td>
                </tr>
                <tr>
                    <td>
                      Bank Group
                    </td>
                    <td>
                      {{$staffFullDetails->bankGroup}}
                    </td>
                </tr>
                <tr>
                    <td>
                      Bank Branch
                    </td>
                    <td>
                      {{$staffFullDetails->bank_branch}}
                    </td>
                </tr>
                <tr>
                    <td>
                      Account No.
                    </td>
                    <td>
                      {{$staffFullDetails->AccNo}}
                    </td>
                </tr>
                <tr>
                    <td>
                      NHF No.
                    </td>
                    <td>
                      {{$staffFullDetails->nhfNo}}
                    </td>
                </tr>
                <tr>
                    <td>
                      Incremental Date
                    </td>
                    <td>
                      {{$staffFullDetails->incremental_date}}
                    </td>
                </tr>
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