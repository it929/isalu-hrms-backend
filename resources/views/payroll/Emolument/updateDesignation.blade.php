@extends('layouts.layout')

@section('pageTitle')
  	PERSONAL EMOLUMENT RECORDS
@endsection

@section('content')
 <div class="box box-default" style="border: none;">
    <div class="box-body box-profile" style="margin:0 5px;">

    <form class="form-horizontal" method="post" action="{{url('/staff/designation/update')}}" enctype="multipart/form-data">
    {{ csrf_field() }}
    	<div class="box-header with-border hidden-print">
          <h3 class="box-title"> <span class="text-center" id='processing'></span></h3>
          <span class="pull-right" style="margin-right: 30px;">
          	 <div style="float: left; width: 100%;">

                <label class="control-label">Staff Name Search</label>
                <input type="text" id="getStaff" name="getStaff" autocomplete="off" list="enrolledUsers"  class="form-control getStaff" value="{{old('getStaff')}}">
		       		<datalist id="enrolledUsers">

				  @foreach($getStaff as $field)

				  	<option value="{{ $field->ID}}">{{ $field->fileNo }}:{{$field->surname}} {{$field->first_name}} {{$field->othernames}}</option>
				  @endforeach
				</datalist>


          	 </div>
          </span>
        </div>

        	<div class="col-md-12 hidden-print">
            @if (count($errors) > 0)
                <div class="alert alert-danger alert-dismissible" role="alert">
                  <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                  </button>
                  <strong>Error!</strong>
                  @foreach ($errors->all() as $error)
                      <p>{{ $error }}</p>
                  @endforeach
                  </div>
                  @endif

                  @if(session('msg'))
                    <div class="alert alert-success alert-dismissible" role="alert">
                        <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                        <strong>Success!</strong> {{ session('msg') }}</div>
                  @endif
                  @if(session('err'))
                    <div class="alert alert-warning alert-dismissible" role="alert">
                        <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                        <strong>Operation Error !<br></strong> {{ session('err') }}</div>
                  @endif
        	</div>


			<div class="row">
				<div align="left" class="col-xs-6">
					<table >
						<tr><td align="left">TO ALL STAFF, </td></tr>
						<tr><td align="left"><br /> ISALU HOSPITAL LIMITED</td></tr>
					</table>
				</div>
				<div align="right" class="col-xs-6">
					<table >
						<tr>
							<td>
								<div ><img src="{{asset('Images/default.png')}}" id="displayPass" height="100"></div>
							</td>
						</tr>
					</table>
				</div>
			</div>

			<h4 class="text-success text-center">
				<strong>Staff Designation Update</strong>
			</h4>



		<div class="row">

		     @if ($CourtInfo->courtstatus==1)
        <div class="col-md-6">
              <div class="form-group">
                <label>Select Court</label>
                <select name="court" id="court" class="form-control" style="font-size: 13px;">
                  <option value="">Select Court</option>
                  @foreach($courts as $court)
                  @if($court->id == session('anycourt'))
                  <option value="{{$court->id}}" selected="selected">{{$court->court_name}}</option>
                @else
                <option value="{{$court->id}}" @if(old('court') == $court->id) selected @endif>{{$court->court_name}}</option>
                @endif
                  @endforeach
                </select>

              </div>
            </div>
          @else
            <input type="hidden" id="court" name="court" value="{{$CourtInfo->courtid}}">
          @endif

            @if ($CourtInfo->divisionstatus==1)
          <div class="col-md-6">
              <div class="form-group">
                <label>Select Division</label>
                <select name="division" id="division_" class="form-control" style="font-size: 13px;">
                <option value="">Select Division</option>
                 @foreach($courtDivisions as $divisions)
                 <option value="{{$divisions->divisionID}}" @if(old('division') == $divisions->divisionID) @endif>{{$divisions->division}}</option>
                 @endforeach
                </select>
               </div>
              </div>
            @else
              <input type="hidden" id="division" name="division" value="{{$CourtInfo->divisionid}}">
            @endif


			<div class="col-sm-12">

				<div style="margin: 0px  5%;">

					<div class="form-group">
					    <label class="control-label col-sm-2" for="fullName">Name:</label>
					    <div class="col-sm-10 row">
					    	<div class="col-sm-4">
					    	    <input type="hidden" id="staffid" name="id" value="">
					    		<input type="text" name="surname" id="surname" value="{{old('surname')}}" class="form-control" placeholder="Surname">
					    	</div>
					    	<div class="col-sm-4">
					    		<input type="text" name="firstName" id="firstName" value="{{old('firstName')}}" required class="form-control" placeholder="First Name">
					    	</div>
					    	<div class="col-sm-4">
					    		<input type="text" name="otherNames" id="otherNames" value="{{old('otherNames')}}" class="form-control" placeholder="Other Names">
					    	</div>
					    </div>
					    <!--//for update-->
					    <div class="col-sm-4">

					    </div>
					</div>


					<div class="form-group">
					    <label class="control-label col-sm-2" >Designation:</label>
					    <div class="col-sm-10">
					    	<select name="designation" id="designation" class="form-control">
					    	<option value="">-Select Designation-</option>
					    	@foreach($desig as $list)
							<option value="{{$list->id}}">{{$list->designation}}-{{$list->grade}}</option>
							@endforeach

					    	</select>
					    </div>
					</div>


				</div>
				<hr />
				<p>

				</p>
			</div>
		</div>



		<div align="center" class="hidden-print"><hr />
			<button type="submit" id="saveUpdate" class="btn btn-success"> <i class="fa fa-save"></i>
			Save/Update</button>
		</div>
	</form>
</div>
</div>
@stop

@section('styles')
<link rel="stylesheet" type="text/css" href="{{asset('assets/css/datepicker.min.css')}}">
@endsection

@section('scripts')
<script src="{{asset('assets/js/jquery-ui.min.js')}}"></script>
  <script type="text/javascript">

  	(function () {
	$('#getStaff').change( function(){

	var fileNo = $('#getStaff').val();
	$('#staffid').val(fileNo);
	//alert(fileNo);
    $('#processing').text('Processing. Please wait...');
		$.ajax({
			url: murl +'/personal-emolument/findStaff',
			type: "post",
			data: {'getStaff': $('#getStaff').val(), '_token': $('input[name=_token]').val()},
			success: function(data){
			console.log(data);
			$('#saveUpdate').attr("disabled", false);
		        $('#processing').text('');
		        $('#surname').val(data.surname);
		        $('#fileNo').val(data.fileNo);
		        $('#firstName').val(data.first_name);
		        $('#otherNames').val(data.othernames);
		        if(data.employee_type == 3)
		        {
		        $('#grade').val("Consolidated");
		        $('#step').val('');
		        }
		        else
		        {
		        $('#grade').val(data.grade);
		        $('#step').val(data.step);
		        }
		        $('#division').val(data.division);
		        $('#state').val(data.stateID);
		        //$('#lga').val(data.lgaID);
		        //var lgadormmy=data.lgaID;
		        $('#bank').append('<option value="'+data.bankID+'" selected>'+data.bank+'</option>');
		        $('#branch').val(data.bank_branch);
		        $('#accountNo').val(data.AccNo);
		        $('#section').append('<option value="'+data.id+'" selected>'+data.depart+'</option>');
		        $('#appointmentfirst').val(data.appointment_date);
		        $('#appointmentDate').val(data.date_present_appointment);
		        $('#confirmationDate').val(data.date_of_confirmation);
		        $('#resumptionDate').val(data.resumption_date);
		        $('#incrementalDate').val(data.incremental_date);
		        $('#lastPromotionDate').val(data.last_promotion_date);
		        $('#dateOfBirth').val(data.dob);
		        $('#residentialAddress').val(data.home_address);
		        $('#qurter').val(data.government_qtr);
		        $('#phoneNumber').val(data.phone);
		        $('#altphoneno').val(data.alternate_phone);
		        $('#email').val(data.email);
		        $('#altemail').val(data.alternate_email);
		        $('#leaveAddress').val(data.leaveaddress);
		        $('#religion').val(data.religion);
		        $('#gender').val(data.gender);
		        $('#mstatus').val(data.maritalstatus);
		         $('#gpz').val(data.gpz);
		         $('#challenge').val(data.challengestatus);
		         $('#challengedetails').val(data.challengedetails);
		         $('#displayPass').attr('src', murl+'/passport/'+data.picture+'');
		         $('#designation').append('<option value="'+data.desigID+'" selected>'+data.designation+'</option>');

			}


		})
	});}) ();
////////////////////////////////////////////////////////
$( function() {
    $("#appointmentDate").datepicker({changeMonth: true,changeYear: true,dateFormat: 'yy-mm-dd'});
    $("#incrementalDate").datepicker({changeMonth: true,changeYear: true,dateFormat: 'yy-mm-dd'});
    $("#dateOfBirth").datepicker({changeMonth: true,changeYear: true,dateFormat: 'yy-mm-dd'});
    $("#appointmentfirst").datepicker({changeMonth: true,changeYear: true,dateFormat: 'yy-mm-dd'});
    $("#confirmationDate").datepicker({changeMonth: true,changeYear: true,dateFormat: 'yy-mm-dd'});
    $("#lastPromotionDate").datepicker({changeMonth: true,changeYear: true,dateFormat: 'yy-mm-dd'});
    $("#resumptionDate").datepicker({changeMonth: true,changeYear: true,dateFormat: 'yy-mm-dd'});
    $("#returnBefore").datepicker({changeMonth: true,changeYear: true,dateFormat: 'yy-mm-dd'});
    $("#failureReturn").datepicker({changeMonth: true,changeYear: true,dateFormat: 'yy-mm-dd'});
  } );

  </script>
  <script>

	//image display
	 function readURL(input) {
        if (input.files && input.files[0]) {
            var reader = new FileReader();

            reader.onload = function (e) {
                $('#displayPass').attr('src', e.target.result);
            }
            reader.readAsDataURL(input.files[0]);
        }
    }
    $("#passport").change(function(){
        readURL(this);
    });

  </script>
  <script type="text/javascript">
  $(document).ready(function()
  {
      $("#clear").click(function () {
    $("#passport").val("");
});
});
                </script>
@stop
