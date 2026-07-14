@extends('layouts.layout')

@section('pageTitle')
  	 RESUMPTION LEAVE REPORT
@endsection

@section('content')
 <div class="box box-default" style="border: none;">
    <div class="box-body box-profile" style="margin:10px 20px;">

		<div class="row">
		    <div class="col-xs-12" style="margin:5px 30px;">
    		    <div class="col-xs-2">
        			<div align="right">
        				<img src="{{ asset('Images/njc-logo.jpg') }}" alt=" " class="img-responsive" width="90" />
        			</div>
    			</div>
    			<div align="left" class="col-xs-10">
        			<div align="center" class="text-success text-center">
        				<h3><strong>ISALU HOSPITAL LIMITED</strong></h3>
        				<h4>ISALU HOSPITAL LIMITED COMPLEX,</h4>
						<h4>THREE ARMS ZONE,</h4>
						<h4>ABUJA</h4>
        			</div>
					<div align="center">
						<br />
						<h4 style="text-decoration: underline;"><strong> RESUMPTIONOF DUTY FORM (JUNIOR OFFICER)</strong></h4>
					</div>
    			</div>
			</div>
			<div class="col-xs-12" style="margin:5px 30px;">
				<div align="left">
					<h4><strong> TO BE COMPLETED BY OFFICER RETURNING FROM ANNUAL LEAVE: </strong></h4>
				</div>
				<div>
					<h4>
						<ol type="6">
							<li>NAME OF OFFICER ................{{ isset($getDetails) && $getDetails ? $getDetails->staff_name : '' }}..................</li>
							<li>RANK ....................{{ isset($getDetails) && $getDetails ? $getDetails->rank : '' }}...............................</</li>
							<li>DATE DEPARTED FOR LEAVE .......................{{ isset($getDetails) && $getDetails ? date('d-M-Y', strtotime($getDetails->departure_date)) : '' }}............................</</li>
							<li>DATE OF RESUMPTION ........................{{ isset($getDetails) && $getDetails ? date('d-M-Y', strtotime($getDetails->resumption_date)) : '' }}...........................</</li>
							<li>POSTING SECTION ........................{{ isset($getDetails) && $getDetails ? $getDetails->posting_section : '' }}...........................</</li>
						</ol>
					</h4>
				</div>
			</div>
			<div class="col-xs-12">
				<div align="right" style="margin-right:20px;">
					<div align="right">
						<h3><strong> ------------------------------------------</strong></h3>
						<div align="right">SIGNATURE OF OFFICER</div>
					</div>
				</div>
			</div>
			<div class="col-xs-12">
				<div align="right" style="margin-right:20px;">
					<div align="right">
						<h3><strong> ------------------------------------------</strong></h3>
						<div align="right">(SUPERVISOR SIGNATURE)</div>
					</div>
				</div>
			</div>


            <div class="col-xs-12">
                <br />
                <br />
                <div align="center" class="bg-gray text-center" style="padding:5px; margin:5px; border-radius: 8px;">
                    <h4>CERTIFICATION</h4>
                </div>
            </div>
            <div class="col-xs-12">
                <h4>
                    I CERTIFY THAT THE ABOVE INFORMATION IS CORRECT RECORD OF SERVICE AND IS HEREBY ENDDORSED ACCORDINGLY.
                </h4>
            </div>
            <div class="col-xs-12">
                <h4>
                    DATED THIS THE.....................................DAY OF.......................................20...................
                </h4>
                <br />
                <div align="center">
                    .................................................................
                    <br />
                    <h4>
                        (CLERICAL OFFICER (RECORD TABLE))
                        <br />
                         ADMINISTRATIONNDEPARTMENT.
                    </h4>
                </div>
            </div>
            <br />

		</div>



    </div>
    </div>
@stop

@section('styles')
    <link rel="stylesheet" type="text/css" href="{{asset('assets/css/datepicker.min.css')}}">
    <style type="text/css">

    </style>
@endsection

@section('scripts')
    <script src="{{asset('assets/js/jquery-ui.min.js')}}"></script>
    <script src="{{ asset('assets/js/jquery.autocomplete.js') }}" ></script>

    <script type="text/javascript">

    </script>
@stop
