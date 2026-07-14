@extends('layouts.layout')

@section('pageTitle')
  	LEAVE FORM/CERTIFICATE
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
        				<h4>LEAVE FORM/CERTIFICATE</h4>
        			</div>
    			</div>
			</div>

			<div class="col-md-12">
			    <br />
    			<div class="col-xs-10">
    			    <div style="margin-left:5px;">
    			        &nbsp; This form should be completed in quaduplicate. Distribution Pattern:
    			        <strong>
    			            <ul>
    			                <li>Personal File of Applicant</li>
    			                <li>Leave Section Copy</li>
    			                <li>Departmental Head's Copy</li>
    			                <li>Officer's Copy</li>
    			            </ul>
    			        </strong>
    			    </div>
    			</div>
    			<div class="col-xs-2">
    			    <div>
    			        <br ><br />
    			       <strong> No....................</strong>
    			    </div>
    			</div>
			</div>
		</div>

		<div class="row">
		    <div class="col-md-12">
		        <div align="center">
		            <h4 style="text-decoration: underline; color:blue;"><strong>SECTION A: &nbsp;&nbsp;&nbsp;&nbsp;   PERSONAL PARTICULARS  </strong></h4>
		        </div>
		        <br />
		    </div>
        </div>

        <div class="row">
		    <div class="col-md-12">
		        <div align="left">
		            <ol type="i">
			             <li>Name: ...................{{ isset($getStaff) && $getStaff ? $getStaff->staff_name : '' }}...................</li>
			             <li>Department: ...................{{ isset($getStaff) && $getStaff ? $getStaff->department : '' }}...................</li>
			             <li>Appointment: ...................{{ isset($getStaff) && $getStaff && (!empty($getStaff->apointment_date)) ? date('d-M-Y', strtotime($getStaff->apointment_date)) : '' }}...................</li>
			             <li>Designation: ...................{{ isset($getStaff) && $getStaff ? $getStaff->designation : '' }}...................</li>
			             <li>Present Section/Unit: ...................{{ isset($getStaff) && $getStaff ? $getStaff->section_or_unit : '' }}...................</li>
			             <li>Basic Salary (on date leave begins): ...................{{ isset($getStaff) && $getStaff ? number_format($getStaff->basic_salary, 2) : '' }}...................</li>
			             <li>Number of days eligible: ...................{{ isset($getStaff) && $getStaff ? $getStaff->eligible_days : '' }}................... </li>
			             <li>Date of First Appointment: ...................{{ isset($getStaff) && $getStaff && (!empty($getStaff->first_appointment_date)) ? date('d-M-Y', strtotime($getStaff->first_appointment_date)) : '' }}...................</li>
			             <li>Date of resumption of duty after last leave: ...................{{ isset($getStaff) && $getStaff && (!empty($getStaff->resumption_date)) ? date('d-M-Y', strtotime($getStaff->resumption_date)) : '' }}................... </li>
			             <li>Registered Domicile: ...................{{ isset($getStaff) && $getStaff ? $getStaff->registered_domicile : '' }}...................</li>
			        </ol>
		        </div>
		    </div>
        </div>

		<div class="row">
		    <div class="col-md-12">
		        <div align="center">
		            <h4 style="text-decoration: underline; color:blue;"><strong>SECTION B: &nbsp;&nbsp;&nbsp;&nbsp;   DETAILS OF APPLICATION </strong></h4>
		        </div>
		    </div>
         </div>

         <div class="row">
		    <div class="col-md-12">
		        <div align="left">
		           <span>
		               <b>2.</b> &nbsp;&nbsp; I hereby apply for .........{{ isset($getStaff) && $getStaff ? $getStaff->noOfDays : '.........'}}.......... days of leave. Starting from ..................{{ isset($getStaff) && (!empty($getStaff->startDate)) ? date('d-M-Y', strtotime($getStaff->startDate)) : '..........' }}...................and to end on ................{{ isset($getStaff) && (!empty($getStaff->endDate)) ? date('d-M-Y', strtotime($getStaff->endDate)) : '..........' }}........
		           </span>
		        </div>
		        <br />
		    </div>
         </div>

        <div class="row">
		    <div class="col-md-12">
		        <div align="center">
		            <h4 style="text-decoration: underline; color:blue;"><strong>SECTION C: &nbsp;&nbsp; RECOMMENDATION FROM THE HEAD OF DEPARTMENT </strong></h4>
		        </div>
		    </div>
         </div>

		 <div class="row">
		    <div class="col-md-12">
		        <div align="left">
		           <span>
		                <b>3.</b> &nbsp;&nbsp;I recommend the application/I do not recommend for the following reasons:
		                <br />
		                  ...........{!! isset($HODComment) ? $HODComment : '................................................................................................................................................................. <br />

		                  ...................................................................................................................................'!!}................................
		                  <br />
		               <br />
		               Date: ..................................... Signature: ...............................................................
		               <div align="center">Head of Department/Unit</div>
		           </span>
		        </div>
		        <br />
		    </div>
         </div>

         <div class="row">
		    <div class="col-md-12">
		        <div align="center">
		            <h4 style="text-decoration: underline; color:blue;"><strong>SECTION D: &nbsp;&nbsp; FROM THE SECRETARY </strong></h4>
		        </div>
		    </div>
         </div>

		<div class="row">
		    <div class="col-md-12">
		        <div align="left">
		           <span>
		                <b>4.</b> &nbsp;&nbsp; Leave approved/Not appoved:
		                ............{!! isset($leaveMattersComment) ? $leaveMattersComment : '.............................................................................................................................................' !!}.......... <br />

		               Date: ..................................... Signature: ...............................................................
		               <div align="center">Secretary</div>
		           </span>
		        </div>
		    </div>
         </div>











        <br /><br />
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
