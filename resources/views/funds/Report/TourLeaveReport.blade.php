<html>
<head>
  <meta charset="utf-8">
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <title>Tour and Leave</title>
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
				<h6 class=" text-center text-success"><strong>REPORT ON TOUR AND LEAVE RECORD</strong></h6>
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
		
					<table class="table table-responsive table-condensed table-striped table-bordered">
               <thead>
                        <tr>
                          <th  rowspan="2">Date Tour Started</th>
                          <th  rowspan="2">Gezette Notice No.</th>
                          <th  rowspan="2">Length of Tour for/oge</th>
                          <th  rowspan="2">Date Due for Leave</th>
                          <th  rowspan="2">Date Departed on Leave</th>
                          <th  rowspan="2">Gazette Notice No.</th>
                          <th  rowspan="2">Date Due to Return from Leave</th>
                          <th  rowspan="2">Date Extension granted to</th>
                          <th  rowspan="2">Salary Rule for Ext.</th>
                          <th  rowspan="2">Date Resumed Duty</th>
                          <th  colspan="2" class="text-center">Passage by<br/>Sea of Air</th>
                          <th  colspan="2" class="text-center">Resident</th>
                          <th  colspan="2" class="text-center">Leave</th>
                        </tr>
                        <tr>
                          <th><small>To UK</small></th>
                          <th><small>Fro UK</small></th>
                          <th><small>Mnths</small></th>
                          <th><small>Days</small></th>
                          <th><small>Mnths</small></th>
                          <th><small>Days</small></th>
                        </tr>
                      </thead>
                      <tbody>
                       @php if($staffFullDetailsTerminationService != null){ @endphp
                           @foreach($staffFullDetailsTerminationService as $tourLeave)
                            <tr>
                              <td>{{date('D M, Y', strtotime($tourLeave->dateTourStarted))}}</td>
                              <td>{{$tourLeave->tourGezetteNumber}}</td>
                              <td>{{$tourLeave->lengthOfTour}}</td>
                              <td>{{date('D M, Y', strtotime($tourLeave->leaveDueDate))}}</td>
                              <td>{{date('D M, Y', strtotime($tourLeave->leaveDepartDate))}}</td>
                              <td>{{$tourLeave->leaveGezetteNumber}}</td>
                              <td>{{date('D M, Y', strtotime($tourLeave->leaveReturnDate))}}</td>
                              <td>{{date('D M, Y', strtotime($tourLeave->dateExtensionGranted))}}</td>
                              <td>{{$tourLeave->salaryRuleForExt }}</td>
                              <td>{{date('D M, Y', strtotime($tourLeave->dateResumedDuty))}}</td>
                              <td>{{$tourLeave->toUK}}</td>
                              <td>{{$tourLeave->fromUK}}</td>
                              <td>{{$tourLeave->residentMonths}}</td>
                              <td>{{$tourLeave->residentDays}}</td>
                              <td>{{$tourLeave->leaveMonths }}</td>
                              <td>{{$tourLeave->leaveDays }}</td>
                            </tr>
                            @endforeach
                         @php } @endphp
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