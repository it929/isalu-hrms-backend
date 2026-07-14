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
	.stylecontrol
	{
		border: none;
		font-weight:700; 
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
	.tbl, .tbl tr, .tbl tr td
	{
		border: none;
		line-height: 30px;
		font-size: 18px;
	}

	/*form  control*/
	.input-group input[type="text"], .input-group .form-control {
    border: none;
    box-shadow: none;
    padding-left: 0;
}
.input-group-addon, .input-group-btn, .input-group .form-control {
    display: table-cell;
}
.input-group .form-control {
    position: relative;
    z-index: 2;
    float: left;
    width: 100%;
    margin-bottom: 0;
}
.form-control, .form-group .form-control {
    border: 0;
    background-size: 0 2px, 100% 1px;
    background-repeat: no-repeat;
    background-position: center bottom,center calc(100% - 1px);
    background-color: transparent;
    transition: background 0s ease-out;
    float: none;
    box-shadow: none;
    border-radius: 0;
    font-weight: 400;
}
.form-control {
    height: 36px;
    padding: 7px 0;
    font-size: 16px;
    line-height: 1.428571429;
    color: #333;
}
button, input, select, a {
    outline: none !important;
}
input {
    -webkit-appearance: textfield;
    -webkit-rtl-ordering: logical;
    user-select: text;
    cursor: auto;
   
}
input, textarea, select, button {
    text-rendering: auto;
    letter-spacing: normal;
    word-spacing: normal;
    text-transform: none;
    text-indent: 0px;
    text-shadow: none;
   
    text-align: start;
    margin: 0em;
   
}
input, textarea, select, button, meter, progress {
    -webkit-writing-mode: horizontal-tb;
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
						
								<div class="col-md-12">

						                     
						         <div class="clear"></div>

                         <form method="post" action="{{url('/offerofappointment/add-acceptance')}}">
                         {{ csrf_field() }}
						<div class="row">
						
						
						<table style="width: 100%">

						<tr>
						<td>
						<div class="col-md-10">
						<p class="phead">
						<h4>The Secretary</h4>
						<h4>Federal Judicial Service Commission</h4>
						<h4>ISALU HOSPITAL LIMITED Complex</h4>
						<h4>Abuja</h4>
						</p>
						
						<hr/>
												
						</div>
						</td>
						
						</tr>
						
						
						</table>
						
						

						</div><!-- end row-->

						<div class="row">
						<div class="col-md-12">
							<h3 style="text-align: center;">UPGRADING TO THE POST OF @if(count($convert))

							
							{{strtoupper($convert->post_considered)}} GL.{{strtoupper($convert->new_grade)}} <br>
                            @else
							--------------------- GL.----------- <br>

							@endif
							{{strtoupper($list->surname)}} {{strtoupper($list->first_name)}} {{strtoupper($list->othernames)}}</h3>
						</div>
						</div>

						<div class="row">
						<table class="tbl" style="width: 100%">
							<tr>
								<td><b>Name</b></td>
								<td>{{$list->surname}} {{$list->first_name}} {{$list->othernames}}</td>	
							</tr>
							<tr>
								<td><b>Present Post</b></td>
								<td>{{$list->Designation}}</td>	
							</tr>
							<tr>
								<td><b>Post For Consideration</b></td>
								<td>@if(count($upgrade)){{$upgrade->post_considered}}@endif</td>	
							</tr>
							<tr>
								<td><b>Additional Qualification</b></td>
								<td>@if(count($upgrade)){{$upgrade->additional_qualification}}@endif</td>	
							</tr>
						</table>
						

						</div><!-- end row-->

						<div class="row text-size" style="margin-top: 30px">
						<h4>SCHOOLS ATTENDED AND QUALIFICATION OBTAINED WITH DATE</h4>
						<table class="table">
						<thead>
							<tr>
								<th>S/N</th>
								<th>Name of Institution</th>
								<th>From</th>
								<th>To</th>
								<th>Qualifications Uptained With Dates</th>
							</tr>
							@php $sn = 1; @endphp
							@foreach($educations as $list)
							<tr>
								<td>{{$sn++}}</td>
								<td>{{$list->schoolattended}}</td>
								<td>{{$list->schoolfrom}}</td>
								<td>{{$list->schoolto}}</td>
								<td>{{$list->degreequalification}}</td>
							</tr>
							@endforeach
						</thead>

						</table>

						</div><!-- end row-->

						<div class="row text-size" style="margin-top: 30px">
						<h4>MEMBERSHIP OF PROFESSIONAL BODIES (IF ANY)</h4>
						<table class="table">
						
							<tr>
								<th>S/N</th>
								<th>Name of Professional Body</th>
								<th>Rank of Membership</th>
								<th>Year of Admission</th>
								
							</tr>

						<tbody>
						@php $sn = 1; @endphp
						@foreach($profbody as $list)
						@if($list->fileNo !='')
							<tr>
							
								<td>{{$sn++}}</td>
								<td>{{$list->name_of_body}}</td>
								<td>{{$list->rank}}</td>
								<td>{{$list->year_admitted}}</td>
							</tr>
							@else
							<td></td>
								<td></td>
								<td></td>
								<td></td>
							</tr>
							@endif
							@endforeach
						</tbody>

						</table>

						</div><!-- end row-->

						<div class="row text-size" style="margin-top: 30px">
						<h4>Work Experience With Dates</h4>
						<table class="table">
						<thead>
							<tr>
								<th>S/N</th>
								<th>Name of Organisation</th>
								<th>Post Held</th>
								<th>From</th>
								<th>To</th>
								<th>Brief Summary of Dutied Performed</th>
							</tr>
							@php $sn = 1; @endphp
							@foreach($previous_work as $list)
							<tr>
								<td>{{$sn++}}</td>
								<td>{{$list->previousSchudule}}</td>
								<td>{{$list->fromDate}}</td>
								<td>{{$list->toDate}}</td>
								<td></td>
								<td></td>
							</tr>
							@endforeach
						</thead>

						</table>

						</div><!-- end row-->
						<hr/>

						<div class="row text-size" style="margin-top: 30px">
						<div class="col-md-12">
							<h3 style="text-align: center;">Recommendation</h3>
						</div>
						<p>@if(count($upgrade)){{$upgrade->recommendations}}@endif
						</p>
						
						<p>
							
							<div class="col-md-5">
								<h5 style="">

								O. A SHOGBOLA (MRS)<BR/>
								CHIEF REGISTRAR
								</h5>
							</div>

						</p>
						</div><!-- end row-->
							

						
						</div>
						</form>

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
  url: "{{ url('/offerofappointment/getdata') }}",

  type: "post",
  data: {'fileno': id},
  success: function(data){
    
    $('#fileno').val(data.fileNo);
    $('#medofficer').val(data.medicalofficer);
    $('#dateaccept').val(data.dateaccepted);
    $('#datecertify').val(data.datecertified);
    $('#bearer').val(data.bearer);
    $('#bearer2').val(data.bearer);
    $('#witness').val(data.witness1);
    $('#witness2').val(data.witness2);
    $('#rank1').val(data.rank1);
    $('#rank2').val(data.rank2);
   $('#certifier').val(data.certifier);
   $('#position').val(data.position);
   $('#address').val(data.address);


    
  }
});
});
});

  $( function() {
	    $("#datecertify").datepicker({
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
				$("#datecertify").val(dateFormatted);
        	},
		});

  } );

    $( function() {
	    $("#dateaccept").datepicker({
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
				$("#dateaccept").val(dateFormatted);
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