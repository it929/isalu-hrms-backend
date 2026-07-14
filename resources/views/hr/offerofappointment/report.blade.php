@extends('layouts.layout')

@section('pageTitle')
 
@endsection

@section('content')
<style type="text/css">
	.form-control
	{
		padding: 5px;
	}
	.form-control
	{
		padding: 5px;
		height: 20px;
	}
	.text-size
	{
		font-size: 17px;
		line-height: 28px;
	}
	.list
	{
		list-style: none;
		padding: 0px;
		margin:0px;
	}
	.list
	{
		
		margin: 10px 6px ;
	}
	#fileid
	{
		height: 34px;
	}
	 h6
	{
		font-weight: bold;
		font-size: 14px;
	}
</style>
 <div class="box box-default" style="border: none;">
    <div class="box-body box-profile" style="border: none;">
    	<div class="box-header with-border hidden-print" style="border: none;">
        	<h3 class="box-title"><b>@yield('pageTitle')</b> <span id='processing'></span></h3>
    	</div>
		 
		  <div class="box-body" style="border: none;">
		        <div class="row">
		            <div class="col-md-12"><!--1st col-->
		                @if (count($errors) > 0)
							<div class="alert alert-danger alert-dismissible" role="alert">
								<button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span>
								</button>
								<strong>Error!</strong> 
								@foreach ($errors->all() as $error)
									<p>{{ $error }}</p>
								@endforeach
							</div>
		                @endif
		                       
						@if(session('msg'))
		                    <div class="alert alert-success alert-dismissible" role="alert">
		                        <button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span>
		                        </button>
		                        <strong>Success!</strong> 
								{{ session('msg') }} 
						    </div>                        
		                @endif

		                @if(session('err'))
		                    <div class="alert alert-warning alert-dismissible" role="alert">
		                        <button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span>
		                        </button>
		                        <strong>Not Allowed ! </strong> 
								{{ session('err') }}
						    </div>                        
		                @endif

		            </div>
					

						</div><!-- end row-->

						<div class="col-md-12">
						                     
						<div class="row">
						<div class="col-md-12">
						<h3 style="text-align: center;">{{strtoupper('Offer Of Appointment To The Pensionable Establisment')}}</h3>
						</div>
                        
                        @foreach($offer as $list)
						<div class="row">
						<table style="width: 100%">
						<tr>
						<td>
						<div class="col-md-5">
							<div class="form-group">
							<h6>
							{{$list->employee_address}}
							</h6>
							</div>
						</div>
						</td>
						
						<td>
						<div class="col-md-5 pull-right">
							<h6><span>Ref. No NICB/455/03/VOLIV/{{$list->fileNo}}</span> <span></span></h6>
							<h6>ISALU HOSPITAL LIMITED</h6>
							<h6>10 PORTH-HARCOURT CRESENT</h6>
							<h6>Area II GARKI</h6>
							<h6>ABUJA</h6>
							<h6>DATE: {{date('d M, Y', strtotime($list->dateissued))}} </h6>
						</div>
						</td>
						</tr>
						</table>

						</div><!-- end row-->

						<div class="row">

						<div class="col-md-3">
						<h6>Sir/Madam</h6>
						</div>

						</div><!-- end row-->

						<div class="row text-size" >

						<div class="col-md-12">
						<p> In the light of the information quoted in your application for appointment dated <b>{{date('d M, Y', strtotime($list->dateofappointment))}}</b> and subject to your passing a medical  examination conducted by a Government Medical Officer as to your fitness for employment in the government Service and showing evidence of successfull vaccination, i have the honour to offer you appointment as {{$list->position}} at commencing salary {{$list->salary}} on the following conditions.
						</p>
						<p>
						<ul class="list">
							<li>(a) That the Appointment will be on probation for two years or for such longer period as may deemed advisable</li>

							<li>(b) That within the probation period, if its is established to the satisfaction of the Head of Department in which you are serving that you have not qualified for efficient service, your appointment may be terminated at any time in accordance with paragraph (C) below without any compenstion other than free transport for yourself only, to the place from which you where engaged and that such free transport will be granted only if your conduct has been good and you claim it within two months of the date of termination of your appointment</li>
							<li>(c) That while you remain on probation, unless you are dismissed the Government may at anytime terminate you engagement by amonth's notice in writing or by payment of month's salary in lieu of notice. </li>
							<li>(d) That at any time unless you are dismissed, you may terminate your engagement by a month's notice in writing or with the consent, in writing of you Head of Department by payment of one month salary in lieu of notice.</li>
							<li>(e) That you will be subject in all respect to all conditions of service stipulated in Public Service Rules and other Government regulations and instructions</li>
							<li>(f) That so long as you remain inthe Government Service you will be liable to be employed in any part of the Federal Republic of Nigeria</li>
						</ul>

						</p>
						<p>2. If you wish to accept this offer i am to request that you will, not later than <b>{{date('d M, Y', strtotime($list->returndate))}} </b> return the attached form Gen. 75 with the Medical Certificate on thereon duly completed by the Medical Officer <b>{{$list->medicalofficer}}</b> and with the Acceptance, Agreement and Declaration thereon each duly complete with your own signature and witnessed, where indicated, by a Governmrnt Officer.
						</p>
						<p>3. I am to add that when presenting yourself to th Medical Officer for examination you should produce this letter as your authority for seeking his signature to the Medical Certificate on Form No. Gen, 75 attached.
						</p>
						</div>

						</div><!-- end row-->
						<div class="row">

						<div class="col-md-4 col-md-offset-4">
						<br/><br/>
						<p> <strong>I am Sir, <br/> Your obedient Servant, <br/><br/><br/> For: Chief Registra</strong></p>

						</div>

						@endforeach

						</div>
						</div>
						
						</div>
							
</div>
@endsection

