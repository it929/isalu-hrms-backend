@extends('layouts.layout')

@section('pageTitle')
  Self Service
@endsection

@section('content')
<div class="row">
	<div class="box-header with-border hidden-print">
		<h3 class="box-title"><b>@yield('pageTitle')</b> <i class="fa fa-arrow-right"></i>
				<strong><em>Personal Emolument Temp Records</em></strong> </h3>
	</div>
</div>
 <div class="box box-default" style="border: none;">
    <div class="box-body box-profile" style="margin:0 5px;">
    <form class="form-horizontal" method="post" action="{{url('/staff/personal-emolument/temp-update')}}" enctype="multipart/form-data">
    {{ csrf_field() }}
    	<div class="box-header with-border hidden-print">
          {{-- <h3 class="box-title"> <span class="text-center" id='processing'></span></h3> --}}
          <span class="pull-right" style="margin-right: 30px;">
          	 <div style="float: left; width: 100%;">

                <label class="control-label">Staff Name Search</label>
                <input type="text" id="getStaff" name="getStaff" autocomplete="off" list="enrolledUsers"  class="form-control getStaff" >
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

		<p>
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
						<tr>
							<td>
								<div>
								<!--!! QrCode::size(100)->generate('Voucher No: '. $list->transactionID.'-'.substr($list->departmentname,0,1).'-NICN') !!-->
								</div>
							</td>
						</tr>
					</table>
				</div>
			</div>
		</p>

		<p>
			<h4 class="text-success text-center">
				<strong>PERSONAL EMOLUMENT RECORDS FOR {{date('Y')}}</strong>
			</h4>
		</p>

		<p>
			<div style="word-break: break-all;">
				In order to comply with the Accountant-General instruction and to maintain correct and comprehensive record of all staff of the Council, <br/> you are requested to complete this Form and return same to the Salary Section before &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
				<input type="text" name="returnBefore" id="returnBefore" style="border: none; font-weight: bold;"
				placeholder="----E.g 25th November, 2017----">
			</div>
		</p>


		<div style="word-break: break-all;">
			<ol start="2">
				<li>
					Failure to return the Form on time may give rise to omitting your name from
					 &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
					 <input type="text" name="failureReturn" id="failureReturn" style="border: none; font-weight: bold;"
				placeholder="----E.g 25th November, 2017----">  Salary Pay Roll
				</li>
				<li>
					Submit along with this Form, one recent passport photograph.
				</li>
			</ol>
		</div>

		<div class="row">
			<div class="col-sm-12">

				<div style="margin: 0px  5%;">
					<div class="form-group">
					    <label class="control-label col-sm-2" for="grade">File Number: </label>
					    <div class="col-sm-10">
					    	<input type="text" name="fileNo" id="fileNo" value="{{old('fileNo')}}" class="form-control" >
					    </div>
					</div>
					<div class="form-group">
					    <label class="control-label col-sm-2" for="fullName">Name:</label>
					    <div class="col-sm-10 row">
					    	<div class="col-sm-4">
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
					    <label class="control-label col-sm-2" for="bank">Gender:</label>
					    <div class="col-sm-3">
					    	<select name="gender" id="gender" class="form-control" required>
					    		<option value="">-Select Gender-</option>
							<option value="Male" >Male</option>
							<option value="Female" >Female</option>
					    	</select>
					    </div>
					    <label class="control-label col-sm-1" for="dateOfBirth">D.O.B:</label>
					    <div class="col-sm-3">
					    	<input type="text" name="dateOfBirth" id="dateOfBirth" value="{{old('dateOfBirth')}}" class="form-control">
					    </div>
					    <label class="control-label col-sm-1" for="bank">Marital Status:</label>
					    <div class="col-sm-2">
					    	<select name="mstatus" id="mstatus" class="form-control">
					    		<option value="">-Select Status-</option>
							<option value="Single" >Single</option>
											  																		  <option value="Married" >Married</option>
											  																		  <option value="Divorced" >Divorced</option>
											  																		  <option value="Widowed" >Widowed</option>
					    	</select>
					    </div>
					</div>
					<div class="form-group">
					    <label class="control-label col-sm-2" for="bank">State:</label>
					    <div class="col-sm-3">
					    	<select name="state" id="state" class="form-control">
					    		<option value="">Select a State</option>
					    		@foreach($statelist as $b)
					    		<option value="{{$b->StateID}}">{{$b->State}}</option>
					    		@endforeach
					    	</select>
					    </div>
					    <label class="control-label col-sm-1" for="bank">LGA:</label>
					    <div class="col-sm-3">
					    	<select name="lga" id="lga" class="form-control">
					    		<option value="">-Select Status-</option>

					    	</select>
					    </div>
					    <label class="control-label col-sm-1" >Geo-political Zone:</label>
					    <div class="col-sm-2">
					    	<select name="gpz" id="gpz" class="form-control">
					    		<option value="">-Select Status-</option>
							<option value="North Central" >North Central</option>
											  																		  <option value="North East" >North East</option>
											  																		  <option value="North West" >North West</option>
											  																		  <option value="South East" >South East</option>
											  																		  <option value="South South" >South South</option>
											  																		  <option value="South West" >South West</option>

					    	</select>
					    </div>
					</div>

					<div class="form-group">
					    <label class="control-label col-sm-2" >Religion:</label>
					    <div class="col-sm-10">
					    	<select name="religion" id="religion" class="form-control">
					    		<option value="">-Select Religion-</option>
							<option value="Christianity" >Christianity</option>
							<option value="Islam" >Islamic</option>
							<option value="Traditional Practice" >Traditional Practice</option>
							<option value="None" >None</option>
							<option value="Others" >Others</option>
					    	</select>
					    </div>
					</div>
					<div class="form-group">
					    <label class="control-label col-sm-2" >Physically Challenged:</label>
					    <div class="col-sm-3">
					    	<select name="challenge" id="challenge" class="form-control">
							<option value="Normal" >Normal</option>
							<option value="Yes" >Yes</option>

					    	</select>
					    </div>

					    <label class="control-label col-sm-2" >State if Any:</label>
					    <div class="col-sm-5">
					    	<textarea name="challengedetails" id="challengedetails" class="form-control">{{old('challengedetails')}}</textarea>
					    </div>
					</div>
					<div class="form-group">
					    <label class="control-label col-sm-2" >Phone No:</label>
					    <div class="col-sm-4">
					    	<input type="text" name="phoneNumber" id="phoneNumber" value="{{old('phoneNumber')}}" class="form-control">
					    </div>

					    <label class="control-label col-sm-2" for="phoneno">Alternative Phone No:</label>
					    <div class="col-sm-4">
					    	<input type="text" name="altphoneno" id="altphoneno" value="{{old('altphoneno')}}" class="form-control">
					    </div>
					</div>
					<div class="form-group">
					    <label class="control-label col-sm-2" >e-mail:</label>
					    <div class="col-sm-4">
					    	<input type="email" name="email" id="email" value="{{old('email')}}" class="form-control">
					    </div>

					    <label class="control-label col-sm-2" >Alternative e-mail:</label>
					    <div class="col-sm-4">
					    	<input type="email" name="altemail" id="altemail" value="{{old('altemail')}}" class="form-control" >
					    </div>
					</div>
					<div class="form-group">
					    <label class="control-label col-sm-2" for="residentialAddress">Residential Address: </label>
					    <div class="col-sm-10">
					    	<textarea name="residentialAddress" id="residentialAddress" class="form-control">{{old('residentialAddress')}} {{Session::get('lg')}}</textarea>
					    </div>
					</div>
					<div class="form-group">
					    <label class="control-label col-sm-2" for="qurter">Home Town Address:</label>
					    <div class="col-sm-10">
					    	<input type="text" name="qurter" id="qurter" value="{{old('qurter')}}" class="form-control">
					    </div>
					</div>
					<div class="form-group">
					    <label class="control-label col-sm-2" for="leaveAddress">Leave Address:</label>
					    <div class="col-sm-10">
					    	<textarea name="leaveAddress" id="leaveAddress" class="form-control">{{old('leaveAddress')}}</textarea>
					    </div>
					</div>
					<div class="form-group">
					    <label class="control-label col-sm-2" for="grade">Grade Level:</label>
					    <div class="col-sm-4">
					    	<input type="text" name="grade" id="grade" value="{{old('grade')}}" class="form-control" required readonly>
					    </div>

					    <label class="control-label col-sm-2" for="grade">Step:</label>
					    <div class="col-sm-4">
					    	<input type="number" name="step" id="step" value="{{old('grade')}}" class="form-control" required readonly>
					    </div>
					</div>

					<div class="form-group">
					    <label class="control-label col-sm-2" for="section">Department:</label>
					    <div class="col-sm-10">
					    	<select name="section" id="section" class="form-control" >
					    		<option value="">Select a Department</option>

					    		@foreach($department as $list)
					    		<option value="{{$list->id}}">{{$list->department}}</option>
					    		@endforeach
					    	</select>
					    </div>
					</div>
					<div class="form-group">
					    <label class="control-label col-sm-2" for="appointmentDate">Date of First Appointment:</label>
					    <div class="col-sm-2">
					    	<input type="text" name="appointmentfirst" id="appointmentfirst" value="{{old('appointmentfirst')}}" class="form-control">

					    </div>

					    <label class="control-label col-sm-2" >Date of Present Appointment:</label>
					    <div class="col-sm-2">
					    	<input type="text" name="appointmentDate" id="appointmentDate" value="{{old('appointmentDate')}}" class="form-control">
					    </div>

					    <label class="control-label col-sm-2" >Date of Confirmation:</label>
					    <div class="col-sm-2">
					    	<input type="text" name="confirmationDate" id="confirmationDate" value="{{old('confirmationDate')}}" class="form-control">
					    </div>
					</div>
					<div class="form-group">
					    <label class="control-label col-sm-2" >Resumption Date:</label>
					    <div class="col-sm-2">
					    	<input type="text" name="resumptionDate" id="resumptionDate" value="{{old('resumptionDate')}}" class="form-control">
					    </div>
					    <label class="control-label col-sm-2" >Last Promotion Date:</label>
					    <div class="col-sm-2">
					    	<input type="text" name="lastPromotionDate" id="lastPromotionDate" value="{{old('lastPromotionDate')}}" class="form-control">
					    </div>
					    <label class="control-label col-sm-2" for="incrementalDate">Incremental Date:</label>
					    <div class="col-sm-2">
					    	<input type="text" name="incrementalDate" id="incrementalDate" value="{{old('incrementalDate')}}" class="form-control">
					    </div>
					</div>


					<div class="form-group">
					    <label class="control-label col-sm-2" for="bank">Bank:</label>
					    <div class="col-sm-3">
					    	<select name="bank" id="bank" class="form-control" required>
					    		<option value="">Select a Bank</option>
					    		@foreach($getBank as $bank)
					    		<option value="{{$bank->bankID}}">{{$bank->bank}}</option>
					    		@endforeach
					    	</select>
					    </div>

					    <label class="control-label col-sm-1" for="branch"> Branch:</label>
					     <div class="col-sm-3">
					    	<input type="text" name="branch" id="branch" value="{{old('branch')}}" class="form-control">
					    </div>

					    <label class="control-label col-sm-1" for="accountNo">Account Number:</label>
					    <div class="col-sm-2">
					    	<input type="text" name="accountNo" id="accountNo" value="{{old('accountNo')}}" class="form-control" required>
					    </div>
					</div>

					<div class="form-group hidden-print">
					    <label class="control-label col-sm-2" for="section">Upload Passport:</label>
					    <div class="col-sm-10">
					    	<input type="file" name="photo" id="passport"/>
					    </div>
					</div>

					<div class="form-group">
						<input type="checkbox" name="certify" checked disabled>
					    <label for="certify">
					    	I hereby certify that the particulars given above are correct to the best of my knowledge.
					    </label>


					</div>
				</div>
				<hr />
				<p>
					<div class="row" align="center">
						<div class="col-sm-12">
							<div align="left" class="col-xs-6">

							</div>

							<div class="col-xs-6 pull-right">
								<table >
									<tr><td align="right">&nbsp;</td></tr>
									<tr><td align="right"> ........................................................</ </td></tr>
									<tr><td align="center">Signature</td></tr>
								</table>
							</div>
						</div>
					</div>
				</p>
			</div>
		</div>

		<!-- Modal Dialog for UPDATE RECORD-->
      	<div class="modal fade" id="confirmUpdate" role="dialog" aria-hidden="true">
      		<div class="modal-dialog">
      			<div class="modal-content">
      				<div class="modal-header">
      					<button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
      					<h4 class="modal-title">PERSONAL EMOLUMENT RECORDS FOR {{date('Y')}}</h4>
      				</div>
      				<div class="modal-body">
      					<p>Are you sure you want to perform this operation?
      					<br />
      					&nbsp; 	<b>Continue</b>  - this will save/update your record <br />
      					&nbsp; 	<b>Cancel</b> 	 - this will return you back to the same page
      					</p>
      				</div>
      				<div class="modal-footer">
      					<button type="button" class="btn btn-default" data-dismiss="modal">Cancel</button>
      					<input type="submit" name="button" class="btn btn-info" value="Continue" >
      				</div>
      			</div>
      		</div>
      	</div>
      	<!-- //Modal Dialog -->

		<div align="center" class="hidden-print"><hr />
			<button type="button" id="saveUpdate" data-toggle="modal" data-target="#confirmUpdate" class="btn btn-success"> <i class="fa fa-save"></i>
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

  		if(fileNo == ""){
			$('#saveUpdate').attr("disabled", true);
		}else{
			$('#saveUpdate').attr("disabled", false);
		}
  	(function () {
	$('#getStaff').change( function(){

	var fileNo = $('#getStaff').val();
	//alert(fileNo);
    $('#processing').text('Processing. Please wait...');
		$.ajax({
			url: murl +'/personal-emolument/findStaffTemp',
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
		        $('#grade').val("Consolidate");
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
		        $('#date_of_confirmation').val(data.date_of_confirmation);
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



  <script type="text/javascript">

  	(function () {
	$('#state').change( function(){

	//alert(fileNo);
    $('#processing').text('Processing. Please wait...');
		$.ajax({
			url: murl +'/personal-emolument/get-lga',
			type: "post",
			data: {'stateId': $('#state').val(), '_token': $('input[name=_token]').val()},
			success: function(data){
			console.log(data);
			//$('#lga').empty();
			/*$.each(data, function(index, obj){
	     		$('#lga').append( '<option value="'+obj.lgaId+'">'+obj.lga+'</option>' );
			}); */

			 $('#processing').text('');
			}
		})
	});}) ();




  	(function () {
	$('.getStaff').change( function(){
	var fileNo = $('.getStaff').val();
	//alert(fileNo);
    $('#processing').text('Processing. Please wait...');
		$.ajax({
			url: murl +'/collect/staff-detail',
			type: "post",
			data: {'staffId': $('.getStaff').val(), '_token': $('input[name=_token]').val()},
			success: function(datas){
			console.log(datas);


			$.each(datas, function(index, obj){

	     		$('#lga').append( '<option value="'+obj.lgaId+'" >'+obj.lga+'</option>' );
			});

			 $('#processing').text('');
			}
		})
	});}) ();


	//appen lga selected value
	(function () {
	$('.getStaff').change( function(){
	var fileNo = $('.getStaff').val();
	//alert(fileNo);
    $('#processing').text('Processing. Please wait...');
		$.ajax({
			url: murl +'/collect/append',
			type: "post",
			data: {'staffId': $('.getStaff').val(), '_token': $('input[name=_token]').val()},
			success: function(data){
			console.log(data);


	     		$('#lga').append( '<option value="'+data.lgaId+'" selected >'+data.lga+'</option>' );


			}
		})
	});}) ();

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
@stop
