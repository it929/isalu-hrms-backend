@extends('layouts.layout')

@section('pageTitle')
  	LEAVE ROASTER APPLICATION
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
        				<h4>ANNUAL LEAVE ROSTER APPLICATION</h4>
        			</div>
    			</div>
			</div>

			<div class="col-md-12">
			    <hr />

				@includeIf('Share.message')

    			<div class="col-md-12">
    			<form method="post" action="{{route('storeLeaveRoaster')}}">
				 @csrf
					<div class="col-md-4">
						<div class="form-group">
							<label for="staffName">Staff Name</label>
							<div class="form-control">{{ isset($details) ? $details->surname .' '. $details->first_name .' '. $details->othernames : 0 }}</div>
						</div>
					</div>
					<div class="col-md-4">
						<div class="form-group">
							<label for="totalLeaveDays">Total Annual Leave Working Days</label>
							<div class="form-control">{{ isset($getLeaveDays) ? $getLeaveDays : 0 }}</div>
							<input type="hidden" name="getleaveDays" value="{{ isset($getLeaveDays) ? $getLeaveDays : 0 }}">
						</div>
					</div>
					{{-- <div class="col-md-4">
						<div class="form-group">
							<label for="totalLeaveDays">Leave Working Days Used</label>
							<div class="form-control">{{ isset($leaveUsed) ? $leaveUsed : 0 }}</div>
							<input type="hidden" name="leaveUsed" value="{{ isset($leaveUsed) ? $leaveUsed : 0 }}">
						</div>
					</div> --}}
					<div class="col-md-4">
						<div class="form-group">
							<label for="grade">Salary Grade Level</label>
							<div class="form-control">{{ isset($details) ? $details->grade : 0 }} </div>
						</div>
					</div>
				</div>
				<div class="col-md-12">
					<div class="col-md-4">
						<div class="form-group">
							<label for="leaveDays">Enter Leave Working Day(s) <span class="text-danger">*</span></label>
							<input type="number" required class="form-control" name="leaveDays" value="{{ isset($daysLeft) ? $daysLeft : 0 }}">
						</div>
					</div>
					<div class="col-md-4">
						<div class="form-group">
							<label for="startDate">Start Date <span class="text-danger">*</span> </label>
							<input type="date" required class="form-control" id="startDate" name="startDate" value="{{old('startDate')}}" >
						</div>
					</div>
					<div class="col-md-4">
						<div class="form-group">
							<label for="department">Department <span class="text-danger">*</span></label>
							<input type="text" readonly class="form-control" value="{{ isset($department) ? $department->staffDept : ''}}">
						</div>
					</div>
				</div>

				<input type="hidden" name="leaveUsed" value="{{ isset($leaveUsed) ? $leaveUsed : 0 }}">
				<input type="hidden" name="department_id" value="{{ isset($department) ? $department->department_id : ''}}">

				<div class="col-md-12">
					<div class="col-md-6">
						<div class="form-group">
							<label for="homeAddress">Leave Address <span class="text-grey">*</span> </label>
							<textarea class="form-control" required name="homeAddress">{{old('homeAddress')}}</textarea>
						</div>
					</div>
					<div class="col-md-6">
						<div class="form-group">
							<label for="description">Description <span class="text-danger">*</span></label>
							<textarea class="form-control" required name="description">{{old('description')}}</textarea>
						</div>
					</div>
				</div>

					<div align="right" class="col-md-12">
						<br />
						<button type="submit" class="btn btn-primary">Send Roaster</button>
						<hr />
					<div>
				</form>

				</div>
			</div>
		</div>

		<table class="table table-responsive table-hover">
			<thead>
				<tr>
					<th>SN</th>
					<th>LEAVE DAYS</th>
					<th>START DATE</th>
					<th>END DATE</th>
					<th>LEAVE ADDRESS</th>
					<th>DESCRIPTION</th>
					<th>STATUS</th>
                    <th>ACTION</th>
				<tr>
			</thead>
			<tbody>
				@if(isset($getRecord) && $getRecord)
					@foreach($getRecord as $key => $value)
						<tr>
							<td>{{ $key + 1 }}</td>
							<td>{{ $value->leaveDays }}</td>
							<td>{{ date('d-M-Y', strtotime($value->startDate)) }}</td>
							<td>{{ date('d-M-Y', strtotime($value->endDate)) }}</td>
							<td>{{ $value->homeAddress }}</td>
							<td>{{ $value->description }}</td>
							<td>
								@if($value->is_submitted)
									@if ($value->is_approved === 1)
										<a href="javascript:;" class="btn btn-sm btn-success disabled" title="This application has been approved"> <i class="fa fa-check" aria-hidden="true"></i> Approved</a>
									@elseif($value->is_approved === 2)
										<span class="badge badge-pill badge-danger" title="This application has been disapproved">Disapproved</span>
									@else
										<a href="javascript:;" class="btn btn-sm btn-primary disabled" title="This application has been submitted"><i class="fa fa-clock-o" aria-hidden="true"></i> Submitted</a>
									@endif
								@else
									<a href="javascript:;" data-toggle="modal" class="btn btn-sm btn-success" data-backdrop="false" data-target="#submitApplication{{$key}}" title="Submit this application"><i class="fa fa-send"></i> Submit</a>
								@endif
							</td>
                            <td>
                                @if ($value->is_submitted)
                                    @if ($value->is_approved === 1)
                                        ..
                                    @elseif ($value->is_approved === 2)
                                        <a href="javascript:;" data-toggle="modal" class="btn btn-sm btn-danger" data-backdrop="false" data-target="#deleteApplication{{$key}}" title="Delete this application"><i class="fa fa-trash"></i></a>
                                        <a href="javascript:;" data-toggle="modal" class="btn btn-sm btn-warning" data-backdrop="false" data-target="#editApplication{{$key}}" title="Edit this application"><i class="fa fa-edit"></i></a>
                                        <a href="javascript:;" data-toggle="modal" class="btn btn-sm btn-success" data-backdrop="false" data-target="#submitApplication{{$key}}" title="Submit this application"><i class="fa fa-send"></i> Submit</a>
                                    @else
                                        <a href="javascript:;" data-toggle="modal" class="btn btn-sm btn-danger" data-backdrop="false" data-target="#deleteApplication{{$key}}" title="Delete this application"><i class="fa fa-trash"></i></a>
                                        <a href="javascript:;" data-toggle="modal" class="btn btn-sm btn-warning" data-backdrop="false" data-target="#editApplication{{$key}}" title="Edit this application"><i class="fa fa-edit"></i></a>
                                    @endif
                                @endif
                            </td>
						</tr>

										<!-- Submit Modal -->
                                            <div class="modal fade text-left d-print-none" id="submitApplication{{$key}}" tabindex="-1" role="dialog" aria-labelledby="myModalLabel12" aria-hidden="true">
                                                <div class="modal-dialog" role="document">
                                                    <div class="modal-content">
                                                        <div class="modal-header bg-gray white">
                                                        <h4 class="modal-title" id="myModalLabel12"><i class="fa fa-exclamation"></i> Confirm </h4>
                                                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                                            <span aria-hidden="true">&times;</span>
                                                        </button>
                                                        </div>
                                                        <div align="center" class="modal-body">
                                                            <div class="text-center">  {{ __('Submit Application Now!!!') }} </div>
                                                            <h5><i class="fa fa-user"></i> {{ __('Are you sure you want to submit this application?')}} </h5>
                                                        </div>
                                                        <div class="modal-footer">
                                                            <button type="button" class="btn grey btn-outline-secondary" data-dismiss="modal">{{ __('Close') }}</button>
                                                            <a href="{{ route('submitApplication', [$value->roasterID])}}" class="btn btn-success">{{ __('Submit') }}</a>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        <!--end Modal-->

										<!-- Delete Modal -->
                                            <div class="modal fade text-left d-print-none" id="deleteApplication{{$key}}" tabindex="-1" role="dialog" aria-labelledby="myModalLabel12" aria-hidden="true">
                                                <div class="modal-dialog" role="document">
                                                    <div class="modal-content">
                                                        <div class="modal-header bg-danger white">
                                                        <h4 class="modal-title" id="myModalLabel12"><i class="fa fa-trash"></i> Confirm </h4>
                                                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                                            <span aria-hidden="true">&times;</span>
                                                        </button>
                                                        </div>
                                                        <div align="center" class="modal-body">
                                                            <div class="text-center text-danger">  {{ __('Delete Application Now!!!') }} </div>
                                                            <h5><i class="fa fa-user"></i> {{ __('Are you sure you want to delete this application?')}} </h5>
                                                        </div>
                                                        <div class="modal-footer">
                                                            <button type="button" class="btn grey btn-outline-secondary" data-dismiss="modal">{{ __('Close') }}</button>
                                                            <a href="{{ route('deleteRoasterApplication', [$value->roasterID])}}" class="btn btn-danger">{{ __('Delete') }}</a>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        <!--end Modal-->

										<!-- Edit Modal -->
                                            <div class="modal fade text-left d-print-none" id="editApplication{{$key}}" tabindex="-1" role="dialog" aria-labelledby="myModalLabel12" aria-hidden="true">
                                                <div class="modal-dialog" role="document">
                                                    <div class="modal-content">
                                                        <div class="modal-header bg-gray white">
                                                        <h4 class="modal-title" id="myModalLabel12"><i class="fa fa-edit"></i> Edit </h4>
                                                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                                            <span aria-hidden="true">&times;</span>
                                                        </button>
                                                        </div>
														<form method="post" action="{{route('updateApplication')}}">
															@csrf
                                                        <div align="left" class="modal-body">

															<div class="row">
																<div class="col-md-12">
																	<div class="col-md-6">
																		<div class="form-group">
																			{{-- <label for="totalLeaveDays">Total Annual Leave Working Days</label>
																			<div class="form-control">{{ isset($getLeaveDays) ? $getLeaveDays : 0 }}</div> --}}
																			<input type="hidden" name="getleaveDays" value="{{ isset($getLeaveDays) ? $getLeaveDays : 0 }}">
																		</div>
																	</div>
																	<div class="col-md-6">
																		<div class="form-group">
																			{{-- <label for="totalLeaveDays">Leave Working Days Used</label>
																			<div class="form-control">{{ isset($leaveUsed) ? $leaveUsed : 0 }}</div> --}}
																			<input type="hidden" name="leaveUsed" value="{{ isset($leaveUsed) ? $leaveUsed : 0 }}">
																		</div>
																	</div>
																</div>

																<div class="col-md-12">
																	<div class="col-md-6">
																		<div class="form-group">
																			<label for="leaveDays">Enter Leave Working Day(s) <span class="text-danger">*</span></label>
																			<input type="number" class="form-control" name="leaveDays" value="{{ $value->leaveDays }}">
																		</div>
																	</div>
																	<div class="col-md-6">
																		<div class="form-group">
																			<label for="startDate">Start Date <span class="text-danger">*</span> </label>
																			<input type="date" required class="form-control" name="startDate" value="{{ $value->startDate  }}" >
																		</div>
																	</div>

																</div>

																<div class="col-md-12">
																	<div class="col-md-6">
																		<div class="form-group">
																			<label for="homeAddress">Home Address <span class="text-grey">(Optional)</span> </label>
																			<textarea class="form-control" name="homeAddress">{{ $value->homeAddress }}</textarea>
																		</div>
																	</div>
																	<div class="col-md-6">
																		<div class="form-group">
																			<label for="description">Description <span class="text-danger">*</span></label>
																			<textarea class="form-control" required name="description">{{ $value->description }}</textarea>
																		</div>
																	</div>
																</div>
																	<hr />
																</div>
															</div>
															<div class="modal-footer">
																<input type="hidden" name="recordID" value="{{$value->roasterID}}" />
																<button type="button" class="btn btn-outline-secondary" data-dismiss="modal">{{ __('Close') }}</button>
																<button type="submit" class="btn btn-success">{{ __('Update') }}</button>
															</div>
														</form>
                                                    </div>
                                                </div>
                                            </div>
                                        <!--end Modal-->

					@endforeach
				@endif
			</tbody>
		</table>
		@if(isset($getRecord) && $getRecord)
			<div align="right" class="col-md-12"><hr />
				Showing {{($getRecord->currentpage()-1)*$getRecord->perpage()+1}}
				to {{$getRecord->currentpage()*$getRecord->perpage()}}
				of  {{$getRecord->total()}} entries
			</div>
			<div class="d-print-none">{{ $getRecord->links() }}</div>
		@endif
    </div>
    </div>
@endsection

@section('styles')
    <link rel="stylesheet" type="text/css" href="{{asset('assets/css/datepicker.min.css')}}">
@endsection

@section('scripts')
    <script src="{{asset('assets/js/jquery-ui.min.js')}}"></script>
    <script src="{{ asset('assets/js/jquery.autocomplete.js') }}" ></script>
	<script src="{{ asset('assets/js/my-hr.js') }}" type="text/javascript"></script>
	{{-- <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.2.1/jquery.min.js"></script> --}}
@endsection
