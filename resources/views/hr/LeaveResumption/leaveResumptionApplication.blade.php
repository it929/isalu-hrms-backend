@extends('layouts.layout')

@section('pageTitle')
  RESUMPTION OF DUTY FORM (JUNIOR OFFICER)
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
        				<h3><strong>SUPREME COURT OF NIGERIA</strong></h3>
        				<h4>RESUMPTION OF DUTY FORM (JUNIOR OFFICER)</h4>
        			</div>
    			</div>
			</div>

			<div class="col-md-12">
			    <hr />

				@includeIf('Share.message')

    			<div class="col-md-12">
    			<form method="post" action="{{route('leave-resumption.store')}}">
				 @csrf
				 	<div class="col-md-4">
						<div class="form-group">
							<label for="staffName">Select Leave you are resuming from <span class="text-danger">*</span> </label>
							<select name="staffLeave" required class="form-control">
								<option value=""> Select </option>
								@if(isset($getLeaveRoaster) && $getLeaveRoaster )
									@foreach($getLeaveRoaster as $key => $leave)
										<option value="{{ $leave->roasterID }}"> Applied for {{ $leave->leaveDays }} day(s) leave from {{ $leave->startDate .' - '. $leave->endDate}} </option>
									@endforeach
								@endif
							</select>
						</div>
					</div>
					<div class="col-md-4">
						<div class="form-group">
							<label for="staffName">User Name</label>
							<div class="form-control">{{ isset($details) ? $details->surname .' '. $details->first_name .' '. $details->othernames : 0 }}</div>
						</div>
					</div>
					<div class="col-md-4">
						<div class="form-group">
							<label for="totalLeaveDays">Rank</label>
							<div class="form-control">{{ isset($details) ? $details->rank : '-' }}</div>
						</div>
					</div>
				</div>
				<div class="col-md-12">
					<div class="col-md-4">
						<div class="form-group">
							<label for="startDate">Date Departed for Leave <span class="text-danger">*</span></label>
							<input type="date" required class="form-control" name="departureDate" value="{{old('startDate')}}" >
						</div>
					</div>
					<div class="col-md-4">
						<div class="form-group">
							<label for="endDate">Date of Resumption <span class="text-danger">*</span>  </label>
							<input type="date" required class="form-control" name="resumptionDate" value="{{old('endDate')}}">
						</div>
					</div>
					<div class="col-md-4">
						<div class="form-group">
							<label for="leaveDays">Posting Section</label>
							<div class="form-control">{{ isset($details) ? $details->staff_section : '-' }}</div>
						</div>
					</div>
				</div>

					<div align="right" class="col-md-12">
						<br />
						<button type="submit" class="btn btn-primary">Create Report</button>
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
					<th>STAFF NAME</th>
					<th>RANK</th>
					<th>POSTING SECTION</th>
					<th>DEPARTMENT DATE</th>
					<th>RESUMPTION DATE</th>
					<th>LEAVE DAYS</th>
					{{-- <th>Created</th> --}}
					<th>STATUS</th>
				<tr>
			</thead>
			<tbody>
				@if(isset($getRecord) && $getRecord)
					@foreach($getRecord as $key => $value)
						<tr>
							<td>{{ $key + 1 }}</td>
							<td>{{ $value->staff_name }}</td>
							<td>{{ $value->rank }}</td>
							<td>{{ $value->posting_section }}</td>
							<td>{{ date('d-M-Y', strtotime($value->departure_date)) }}</td>
							<td>{{ date('d-M-Y', strtotime($value->resumption_date)) }}</td>
							<td>{{ $value->staff_leave_days . ' Day(s)' }}</td>
							{{-- <td>{{ date('d-M-Y', strtotime($value->created_at)) }}</td> --}}
							<td>
								@if($value->is_submitted)
									<a href="javascript:;" class="btn btn-sm btn-info" title="This application as been submitted"><i class="fa fa-save"></i> Submitted</a>
								@else
									<a href="javascript:;" data-toggle="modal" class="btn btn-sm btn-danger" data-backdrop="false" data-target="#deleteApplication{{$key}}" title="Delete this application"><i class="fa fa-trash"></i></a>
									<a href="javascript:;" data-toggle="modal" class="btn btn-sm btn-warning" data-backdrop="false" data-target="#editApplication{{$key}}" title="Edit this application"><i class="fa fa-edit"></i></a>
									<a href="javascript:;" data-toggle="modal" class="btn btn-sm btn-success" data-backdrop="false" data-target="#submitApplication{{$key}}" title="Submit this application"><i class="fa fa-send"></i> Submit</a>
								@endif
								<a href="{{ route('resumptionReport', ['id'=>$value->id]) }}" target="_black" class="btn btn-sm btn-info" title="View Report"> View</a>
							</td>
						</tr>
								<!--Delete Model -->
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
                                                            <a href="{{ route('deleteResumption', [$value->id])}}" class="btn btn-danger">{{ __('Delete') }}</a>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        <!--end Modal-->

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
                                                            <a href="{{ route('submiteResumption', [$value->id])}}" class="btn btn-success">{{ __('Submit') }}</a>
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
														<form method="post" action="{{route('leave-resumption.update')}}">
															@csrf
                                                        <div align="left" class="modal-body">

															<div class="row">
																<div class="col-md-12">
																		<div class="form-group">
																			<label for="staffName">Select Leave you are resuming from <span class="text-danger">*</span> </label>
																			<select name="staffLeave" required class="form-control">
																				<option value=""> Select </option>
																				@if(isset($getLeaveRoaster) && $getLeaveRoaster )
																					@foreach($getLeaveRoaster as $key => $leave)
																						<option value="{{ $leave->roasterID }}" {{$value->leave_roasterID == $leave->roasterID ? 'selected' : '' }}> Applied for {{ $leave->leaveDays }} day(s) leave from {{ $leave->startDate .' - '. $leave->endDate}} </option>
																					@endforeach
																				@endif
																			</select>
																		</div>
																</div>
																<div class="col-md-12">
																	<div class="col-md-6">
																		<div class="form-group">
																			<label for="startDate">Date Departed for Leave <span class="text-danger">*</span></label>
																			<input type="date" required class="form-control" name="departureDate" value="{{ $value->departure_date }}" >
																		</div>
																	</div>
																	<div class="col-md-6">
																		<div class="form-group">
																			<label for="endDate">Date of Resumption <span class="text-danger">*</span>  </label>
																			<input type="date" required class="form-control" name="resumptionDate" value="{{ $value->resumption_date }}">
																		</div>
																	</div>
																</div>
															</div>

															<div class="modal-footer">
																<input type="hidden" name="recordID" value="{{$value->id}}" />
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
