@extends('layouts.layout')

@section('pageTitle')
  Staff Status/Transfer
@endsection

@section('content')

  <form method="post" action="{{ url('/staffStatus/update') }}">
  <div class="box-body">
        <div class="row">
            <div class="col-md-12"><!--1st col-->
            
            <h2 style="margin-bottom:20px">Change Staff Status</h2>
            
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

            </div>
			{{ csrf_field() }}
            
				<input type="hidden" name="codeID" id="codeID">

				<div class="col-md-12"><!--2nd col-->
					<div class="row">
						<div class="col-md-6">
							<div class="form-group">
								<label for="month">Select Staff Name</label>
								<input type="text"  name="staffName" id="staffName" autocomplete="off" list="enrolledUsers"  class="form-control"  >
                						<datalist id="enrolledUsers">
                					  @foreach($staffList as $staff)
										<option value="{{$staff -> ID}}">
											{{$staff -> surname . ' ' . $staff -> first_name . ' ' . $staff -> othernames}}
										</option>
									@endforeach
                					</datalist>	
								<!--<select name="staffName" id="staffName" class="form-control">
									<option></option>
									@foreach($staffList as $staff)
										<option value="{{$staff -> ID}}">
											{{$staff -> surname . ' ' . $staff -> first_name . ' ' . $staff -> othernames}}
										</option>
									@endforeach
								</select>-->
							</div>
						</div>	

						<div class="col-md-6">
							<div class="form-group">
								<label for="month">Staff Full Name</label>
								<input type="text" name="name" id="name" readonly class="form-control" />
							</div>
						</div>
					</div>	

					<input type="hidden" id="dID" name="divisionID">

					<div class="row">
						<div class="col-md-6">
							<div class="form-group">
								<label for="month">Operation Type</label>
								<div class="row">
									<div class="col-md-6">
										<div class="form-control">
											<input type="radio" value="1" name="radio" id="radio1" checked="checked" /> &nbsp; Status
										</div>
									</div>
									<div class="col-md-6">
										<div class="form-control">
											<input type="radio" value="2" name="radio" id="radio2" /> &nbsp; Transfer
										</div>
									</div>
								</div>
								
							</div>
						</div>	

						<div class="col-md-6">
							<div class="form-group">
								<label for="month">File Number</label>
								<input type="text" name="fileNo" id="fileNo" readonly class="form-control" />
							</div>
						</div>
					</div>	

					<div class="row">
						<div class="col-md-6" id="operation1"><!--Employee Status-->
							<div class="form-group">
								<label for="month">Staff Status</label>
								<select name="staffStatus" id="staffStatus" class="form-control">
									<option></option>
									<option value="active service">Active Service</option>
									<option value="contract service">Contract Service</option>
									<option value="dismissal">Dismissal</option>
									<option value="maternity leave">Maternity Leave</option>
									<option value="study leave">Study Leave</option>
									<option value="temporary leave">Temporary Leave</option>
									<option value="resignation">Resignation</option>
									<option value="retirement">Retirement</option>
									<option value="temporary suspension">Temporary Suspension</option>
									<option value="deceased">Deceased</option>
									<option value="termination">Termination</option>
								</select>
							</div>
						</div>

						<div class="col-md-6" id="operation2"><!--Division-->
							<div class="form-group">
								<label for="month">Staff Division</label>
								<select name="staffDivision" id="staffDivision" class="form-control">
									<option></option>
									@foreach($division as $division)
										<option value="{{$division->divisionID}}">
											{{$division->division}}
										</option>
									@endforeach
								</select>
							</div>
						</div>		

						<div class="col-md-6">
							<div class="form-group">
								<label for="month">&nbsp;</label><br />
								<input name="action" id="action" class="btn btn-success" type="submit" value="Update Staff Record" />
							</div>
						</div>
					</div>	

				</div>
        </div><!-- /.col -->
    </div><!-- /.row -->
  </form>
@endsection

@section('scripts')
<script src="{{asset('assets/js/jquery-ui.min.js')}}"></script>
  <script type="text/javascript">
	$('#operation2').hide();
  	(function () {
	$('#radio1').change( function(){
		if ($('#radio1').val() == 1){
			$('#operation1').show();
			$('#operation2').hide();
			$('#action').val('Update Staff Record');
		}
	});}) ();

	(function () {
	$('#radio2').change( function(){
		if ($('#radio2').val() == 2){
			$('#operation2').show();
			$('#operation1').hide();
			$('#action').val('Transfer Staff');
		}
	});}) ();

	(function () {
	  $('#staffName').change( function(){
		$.ajax({
			url: murl +'/staffStatus/findStaff',
			type: "post",
			data: {'staffName': $('#staffName').val(), '_token': $('input[name=_token]').val()},
			success: function(data){
					$('#name').val(data.surname + ' ' + data.first_name + ' ' + data.othernames);
					$('#fileNo').val(data.fileNo);
					$('#dID').val(data.divisionID);
			}
		})	
	});}) ();


</script>
@endsection