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
	.text-size .col-md-12 p
	{
		font-size: 18px;
		line-height: 32px;
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
	.addr p
	{
		text-align: right;

	}
	.addr
	{
		background: #000;
	}
</style>
 <div class="box box-default" style="border:none;">
    <div class="box-body box-profile" style="border:none;">
    	<div class="box-header with-border hidden-print" style="border:none;">
        	<h3 class="box-title"><b>@yield('pageTitle')</b> <span id='processing'></span></h3>
    	</div>
		 
		  <div class="box-body" style="border:none;">
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
						

						                     
						            		 <div class="clear"></div>
						  <div class="col-md-12">
						  @foreach($medical as $med)
                         <form method="post" action="{{url('/offerofappointment/add')}}">
                         {{ csrf_field() }}
						<div class="row">
						
						
						<table style="width: 100%">

						<tr>
						<td>
						<div class="col-md-6">
						<p style="padding-top: 230px">
						<span>01-2691703291761</span>
						<h6><span>Ref. No NICB/455/03/VOL. IV/</span> <span>{{$med->fileNo}}</span></h6>
						</p>
						<p>
						The Chief Medical Officer,
						</p>
						<p>
							Government Hospital
						</p>
						<p>
						{{$med->hospital}}
						</p>
						<p>
						Mr {{$med->medofficername}}
						</p>

						
						</div>
						</td>
						<td>

						<div class="col-md-6 pull-right">
						
						<div class="addrs">
						<p>ISALU HOSPITAL LIMITED</p>
						<p>Abuja Office</p>
						<p>10 Port-Harcourt Crescent,</p>
						<p>Area II Garki</p>
						<p>Abuja</p>
						</div>
						
					
						<div class="addrs">
							<p>ISALU HOSPITAL LIMITED</p>
							<p>P. M. P 12768</p>
							<p>Ikoyi</p>
							<p>Lagos</p>
						</div>
						<h6>{{$med->dateissued}}</h6>
						</div>
						</td>
						
						</tr>
						
						
						</table>
						
						

						</div><!-- end row-->

						<div class="row">
						<div class="col-md-12">
							<h2 style="text-align: center">MEDICAL EXAMINATION FOR CONFIRMATION OF APPOINTMENT</h2>
						</div>
						</div>

						<div class="row">

						<div class="col-md-3">
						
						</div>

						</div><!-- end row-->

						<div class="row text-size" >

						<div class="col-md-12">
						<p> 
						I am directed to recommed the bearer <b>{{$med->bearername}}</b> a member of staff of this Court Medical Examination with view to confirming his/her appointment. 
						</p>
						<p>2. Attached hereto a copy of Gen. 75 for neccessary action</p>
						<p>3. Grateful for you kind co-operation</p>
						</div>
						<div class="col-md-3 pull-right">
						<p><b>{{$med->signaturename}}</b></p>
						<p>For: Chief Registrar</p>
						</div>

						</div><!-- end row-->
						</table>

						
						</div>
						</form>
						@endforeach
						</div>
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
  url: "{{ url('/offerofappointment/medicalexam') }}",

  type: "post",
  data: {'fileno': id},
  success: function(data){
    
    $('#fileno').val(data.fileNo);
    $('#hospitalname').val(data.hospital);
    $('#dateissued').val(data.dateissued);
    $('#medofficername').val(data.medofficername);
    $('#bearername').val(data.bearername);
    $('#signname').val(data.signaturename);
    $('#position').val(data.position);
    $('#file').val(data.fileNo);
    $('#emolument').val(data.emolument);

    
  }
});
});
});

  $( function() {
	    $("#dateissued").datepicker({
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
				$("#dateissued").val(dateFormatted);
        	},
		});

  } );

    $( function() {
	    $("#acceptdate").datepicker({
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
				$("#acceptdate").val(dateFormatted);
        	},
		});

  } );

      $( function() {
	    $("#effectdate").datepicker({
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
				$("#effectdate").val(dateFormatted);
        	},
		});

  } );
$( function() {
	    $("#refdate").datepicker({
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
				$("#refdate").val(dateFormatted);
        	},
		});

  } );
</script>
@endsection