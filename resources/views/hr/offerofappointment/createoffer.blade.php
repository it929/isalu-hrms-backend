@extends('layouts.layout')

@section('pageTitle')
 {{strtoupper('Offer Of Appointment To The Pensionable Establisment')}}

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

	.stylecontrol
	{
		border: none;
		font-weight:700;
		outline: none;
	}
	.p-style
	{
		font-weight: 400;
		font-size: 17px;
	}
	.box-header.with-border
	{
		border: none;
	}
	.box-profile, .box-default, .box-body
	{
		border: none;
	}
	.phead h4
	{
		font-size: 16px;

	}
	#fileid
	{
		width:40%;
	}

	 @media screen, print {
         .form-control
         {
         	width:100 !important;
         }
      }

</style>
 <div class="box box-default">
    <div class="box-body box-profile">
    	<div class="box-header with-border hidden-print">
        	<h3 class="box-title"><b>@yield('pageTitle')</b> <span id='processing'></span></h3>
    	</div>

		  <div class="box-body">
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
												{{$candidate->surname ?? ''}} {{$candidate->first_name ?? ''}} {{$candidate->othernames ?? ''}}
						                      <!--<form method="post" action="" class="hidden-print">
                                             {{ csrf_field() }}
                                             <label>Select Staff</label>
                                             <select name="fileid" id="fileid" class="form-control">
											<option value="0" selected="selected">Choose one</option>
											@foreach($perList as $prolist)

									                  <option value="{{$prolist->fileNo}}">{{$prolist->surname }}  {{$prolist->first_name}}</option>
									                  @endforeach


								                  </select>
						            		 </form>
						            		-->
						            		 <div class="clear"></div>

                         <form method="post" action="{{url('/offerofappointment/save')}}">
                         {{ csrf_field() }}

                         <input type="hidden" name="candidateID" value="{{$candidate->cid ?? ''}}">
						<div class="row">
						<table style="width: 100%">
						<tr>
						<td>
						<div class="col-md-10">
							<div class="form-group">

							<textarea name="address" id="address" class="form-control stylecontrol" style="overflow:hidden; height: 90px;" placeholder="______________________________________________________________________________________________________________________________________________________________________________"></textarea>
							</div>
						</div>
						</td>
						<td>
						<div class="col-md-5 addr pull-right">
							<h6><span>Ref. No NICB/455/03/VOLIV/</span> <span><input type="text" name="fileno" id="fileno" class="form-control stylecontrol" style="width:20%; display: inline;" placeholder="___________________"></span></h6>
							<h6>ISALU HOSPITAL LIMITED</h6>
							<h6>THREE ARMS ZONE, CENTRAL DISTRICT PMB 308, ABUJA</h6>
							<h6>ISALU HOSPITAL LIMITED COMPLEX</h6>
							<h6>ABUJA</h6>
							<h6>DATE: <input type="text" name="date" id="date" class="form-control stylecontrol" style="width:45%; display: inline;" placeholder="_______________________"></h6>
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
						<p> In the light of the information quoted in you application for appointment dated <input type="text" name="offerdate" id="dateoffer" class="form-control stylecontrol" style="width:10%; display: inline;" placeholder="_______________________________" value="{{$candidate->dateofappointment ?? ''}}"> and subject to your passing a medical  examination conducted by a Government Medical Officer as to your fitness for employment in the government Service and showing evidence of successfull vaccination, i have the honour to offer you appointment as <input type="text" name="position" id="position" class="form-control stylecontrol" style="width:13%; display: inline;" placeholder="___________________________" value="{{$candidate->position ?? ''}}"> at commencing salary <input type="text" name="salary" id="salary" class="form-control stylecontrol" style="width:10%; display: inline;" placeholder="___________________________" value="{{number_format($candidate->salary,2) ?? ''}}"> on the following conditions.
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
						<p>2. If you wish to accept this offer i am to request that you will, not later than <input type="text" name="returndate" class="form-control stylecontrol" id="returndate" style="width:10%; display: inline;" placeholder="_______________________________" value="{{$candidate->returndate ?? ''}}"> return the attached form Gen. 75 with the Medical Certificate on thereon duly completed by the Medical Officer <input type="text" name="medofficer" id="medofficer" class="form-control stylecontrol" style="width:10%; display: inline;" placeholder="___________________________" value="{{$candidate->medicalofficer ?? ''}}"> and with the Acceptance, Agreement and Declaration thereon each duly complete with your own signature and witnessed, where indicated, by a Governmrnt Officer.
						</p>
						<p>3. I am to add that when presenting yourself to th Medical Officer for examination you should produce this letter as your authority for seeking his signature to the Medical Certificate on Form No. Gen, 75 attached.
						</p>
						</div>

						</div><!-- end row-->

						<div class="row">

						<div class="col-md-4 col-md-offset-4">
						<br/><br/>

						<p> <strong>I am Sir, <br/> Your obedient Servant, <br/><br/><br/>
						<input type="text" name="for_registrar" class="form-control stylecontrol" id="for_registrar" style="width:60%; display: inline;" placeholder="_______________________________" value="{{$candidate->for_registrar ?? ''}}">
						<br/>

						 For: Executive Secretary</strong></p>

						</div>
						</div>


						<div class="row">
								<div class="col-md-12">

								<div class="col-md-3">
									<div align="left" class="form-group">
										<label for="month">&nbsp;</label><br />
										<!--<input type="submit" name="print" class="btn btn-success hidden-print" value="Print">-->
									</div>
								</div>

								<div class="col-md-9">
									<div align="right" class="form-group">
										<label for="month">&nbsp;</label><br />
										<input type="submit" name="add" class="btn btn-success hidden-print" value="Add">
										<!--<input type="submit" name="update" class="btn btn-success hidden-print" value="Update">	-->
									</div>
								</div>


								</div>
							</div><!-- end row-->

						</div>
						</form>
						</div>

</div>
@endsection

@section('styles')
<link rel="stylesheet" type="text/css" href="{{asset('assets/css/datepicker.min.css')}}">
@endsection

@section('scripts')
<script src="{{asset('assets/js/jquery-ui.min.js')}}"></script>
<script src="{{asset('assets/js/datepicker_scripts.js')}}"></script>


  <script type="text/javascript">


$( function() {

  $("#fileid").on('change', function(){

//$('#nameID').val(suggestion.data);
var id = $(this).val();
//alert(id);
$token = $("input[name='_token']").val();
$.ajax({
	headers: {'X-CSRF-TOKEN': $token},
  url: "{{ url('/offerofappointment/getfileno') }}",

  type: "post",
  data: {'fileno': id},
  success: function(data){

    $('#fileno').val(data.fileNo);
    $('#address').val(data.employee_address);
    $('#date').val(data.dateissued);
    $('#dateoffer').val(data.dateofappointment);
    $('#returndate').val(data.returndate);
    $('#medofficer').val(data.medicalofficer);
    $('#salary').val(data.salary);
    $('#position').val(data.position);
    $('.fileno').val(data.fileNo);
    $('#for_registrar').val(data.for_registrar);


  }
});
});
});

  $( function() {
	    $("#date").datepicker({
	    	changeMonth: true,
	    	changeYear: true,
	    	yearRange: '1910:2090', // specifying a hard coded year range
		    showOtherMonths: true,
		    selectOtherMonths: true,
		    dateFormat: "dd MM, yy",
		    //dateFormat: "D, MM d, yy",
		    onSelect: function(dateText, inst){
		    	var theDate = new Date(Date.parse($(this).datepicker('getDate')));
				var dateFormatted = $.datepicker.formatDate('dd MM yy', theDate);
				$("#date").val(dateFormatted);
        	},
		});

  } );

    $( function() {
	    $("#dateoffer").datepicker({
	    	changeMonth: true,
	    	changeYear: true,
	    	yearRange: '1910:2090', // specifying a hard coded year range
		    showOtherMonths: true,
		    selectOtherMonths: true,
		    dateFormat: "dd MM, yy",
		    //dateFormat: "D, MM d, yy",
		    onSelect: function(dateText, inst){
		    	var theDate = new Date(Date.parse($(this).datepicker('getDate')));
				var dateFormatted = $.datepicker.formatDate('dd MM yy', theDate);
				$("#dateoffer").val(dateFormatted);
        	},
		});

  } );

      $( function() {
	    $("#returndate").datepicker({
	    	changeMonth: true,
	    	changeYear: true,
	    	yearRange: '1910:2090', // specifying a hard coded year range
		    showOtherMonths: true,
		    selectOtherMonths: true,
		    dateFormat: "dd MM, yy",
		    //dateFormat: "D, MM d, yy",
		    onSelect: function(dateText, inst){
		    	var theDate = new Date(Date.parse($(this).datepicker('getDate')));
				var dateFormatted = $.datepicker.formatDate('dd MM yy', theDate);
				$("#returndate").val(dateFormatted);
        	},
		});

  } );


//Auto resize input
$(document).ready(function()
{
	var input = document.querySelector('input'); // get the input element
    input.addEventListener('input', resizeInput); // bind the "resizeInput" callback on "input" event
    resizeInput.call(input); // immediately call the function

	function resizeInput() {
	  this.style.width = this.value.length + "ch";
	}
});

</script>
@endsection
