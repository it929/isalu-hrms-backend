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
	
</style>
 <div class="box box-default" style="border:none;">
    <div class="box-body box-profile" style="border:none;">
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
						

						                      <form method="post" action="" class="hidden-print">
                                             {{ csrf_field() }}
                                             <label>Select Eployee</label>
                                             <select name="fileid" id="fileid" class="form-control">
											<option value="0" selected="selected">Choose one</option>
											@foreach($perList as $prolist)

									                  <option value="{{$prolist->fileNo}}">{{$prolist->surname }}  {{$prolist->first_name}}</option>
									                  @endforeach
									                  
									                  
								                  </select>	
						            		 </form>
						            		 <div class="clear"></div>

                         <form method="post" action="{{url('/offerofappointment/add')}}">
                         {{ csrf_field() }}
						<div class="row">
						
						
						<table style="width: 100%">

						<tr>
						<td>
						<div class="col-md-6">
						<p style="padding-top: 230px;">
						<span>01-2691703291761</span>
						<h6><span>Ref. No NICB/455/03/VOL. IV/</span> <span><input type="text" name="fileno" id="fileno" class="form-control stylecontrol" style="width:40%; display: inline;" placeholder="______________________"></span></h6>
						</p>
						The Chief Medical Officer,
						<p>
						<p>
							Government Hospital
						</p>
						<p>
						<input type="text" name="hospitalname" id="hospitalname" class="form-control stylecontrol" style="width:40%; display: inline;" placeholder="______________________________">
						</p>
						<p>
						Mr
						  <input type="text" name="medofficername" id="medofficername" class="form-control stylecontrol" style="width:40%; display: inline;" placeholder="___________________________________">
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
                       </div>
						</td>
						</tr>
						<tr>
						<td>

							
						<h6><input type="text" name="dateissued" id="dateissued" class="form-control stylecontrol" style="width:60%; display: inline;" placeholder="_________________________________________"></h6>
							</td>
						</tr>
						</table>
						</div>
						

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
						I am directed to recommend the bearer <input type="text" name="bearername" id="bearername" class="form-control stylecontrol" style="width:20%; display: inline;" placeholder="____________________________"> a member of staff of this Court Nedical Examination with view to confirming his/her appointment. 
						</p>
						<p>2. Attached hereto a copy of Gen. 75 for neccessary action</p>
						<p>3. Grateful for you kind co-operation</p>
						</div>
						<div class="col-md-3 pull-right">
						<p><input type="text" name="signname" id="signname" class="form-control stylecontrol" style="width:80%; display: inline;" placeholder="_______________________________"></p>
						<p>For: Chief Registrar</p>
						</div>

						</div><!-- end row-->
						</table>

						<div class="row">
								<div class="col-md-12">

								<div class="col-md-3">
									<div align="left" class="form-group">
										<label for="month">&nbsp;</label><br />
										<input type="submit" name="print" class="btn btn-success hidden-print" value="Print">
									</div>
								</div>
								
								<div class="col-md-9">
									<div align="right" class="form-group">
										<label for="month">&nbsp;</label><br />
										<input type="submit" name="add" class="btn btn-success hidden-print" value="Add">
										<input type="submit" name="update" class="btn btn-success hidden-print" value="Update">	
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
  url: "{{ url('/offerofappointment/medicalexam') }}",

  type: "post",
  data: {'fileno': id},
  success: function(data){
    
    $('#fileno').val(data.fileNo);
    $('#hospitalname').val(data.hospital);
    $('#dateissued').val(data.dateissued);
    $('#medofficername').val(data.medofficername);
    //$('#bearername').val(data.bearername);
    $('#signname').val(data.signaturename);
    $('#position').val(data.position);
    $('#file').val(data.fileNo);
    $('#emolument').val(data.emolument);
   // $('#bearername').val(data.surname+', '+data.first_name);

    
  }
});

$.ajax({
	headers: {'X-CSRF-TOKEN': $token},
  url: "{{ url('/offerofappointment/bearername') }}",

  type: "post",
  data: {'fileno': id},
  success: function(data){
       
    $('#bearername').val(data[0].surname+', '+data[0].first_name);
   
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