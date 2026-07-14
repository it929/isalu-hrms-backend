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
                        <form method="get" action='{{ url('/fetch-staff-l-application') }}'>

                            <div class="col-md-12">

                                <div class="col-md-12">
                                    {{-- <div class="form-group">
                                        <label for="department">Staff ID/No. <span class="text-danger">*</span></label>
                                        <input type="text" name="staffID" id="staffID" class="form-control"
                                            placeholder="Staff Name">
                                    </div> --}}

                                    <label for="staffID"> Search staff by ID or Name
                                        <select name="staffID" id="staffID"
                                            class="pickStaffDetail form-select form-select-lg" style="width: 100%">
                                            <option selected disabled>Pick Staff</option>
                                            @foreach ($staffDetails as $item)
                                                @if (!empty($item->othernames))
                                                    <option value="{{ $item->ID }}">
                                                        {{ $item->fileNo . ' | ' . $item->first_name . ' ' . $item->surname . ' ' . $item->othernames }}
                                                    </option>
                                                @else
                                                    <option value="{{ $item->ID }}">
                                                        {{ $item->fileNo . ' | ' . $item->first_name . ' ' . $item->surname }}
                                                    </option>
                                                @endif
                                            @endforeach
                                        </select>
                                    </label>

                                </div>

                            </div>

                            <div align="right" class="col-md-12">
                                <br />
                                <button type="submit" class="btn btn-primary">Search Staff</button>
                                <hr />
                                <div>
                        </form>

                    </div>

                    @if (session('no-roaster-data'))
                        <div class="alert alert-danger" role="alert">
                            <strong>{{ session('no-roaster-data') }}</strong>
                        </div>
                    @endif


                </div>
            </div>

            <div class="card" style="text-align: center; font-weight:bold;">
                <h5>LEAVE ROASTER FOR THE YEAR @php echo date('Y') @endphp</h5>
            </div>
            <table class="table table-responsive table-hover">
                <thead>
                    <tr>
                        <th>SN</th>
                        <th>STAFF NAME</th>
                        <th>LEAVE DAYS</th>
                        <th>START DATE</th>
                        <th>END DATE</th>
                        <th>HOME ADDRESS</th>
                        <th>DESCRIPTION</th>
                        <th>STATUS</th>
                    <tr>
                </thead>
                <tbody>
                    @if (isset($getRecord) && $getRecord )
                        @foreach ($getRecord as $key => $value)
                            <tr>
                                <td>{{ $key + 1 }}</td>
                                <td>{{ $value->staff_name }}</td>
                                <td>{{ $value->leaveDays }}</td>
                                <td>{{ date('d-M-Y', strtotime($value->startDate)) }}</td>
                                <td>{{ date('d-M-Y', strtotime($value->endDate)) }}</td>
                                <td>{{ $value->homeAddress }}</td>
                                <td>{{ $value->description }}</td>
                                <td>
                                    @if ($value->is_submitted)
                                        @if ($value->is_approved === 1)
                                            <a href="javascript:;" class="btn btn-sm btn-success disabled"
                                                title="This application has been approved"> <i class="fa fa-check"
                                                    aria-hidden="true"></i> Approved</a>
                                        @elseif($value->is_approved === 2)
                                            <span class="badge badge-pill badge-danger"
                                                title="This application has been disapproved">Disapproved</span>
                                        @else
                                            <a href="javascript:;" class="btn btn-sm btn-primary disabled"
                                                title="This application has been submitted"><i class="fa fa-clock-o"
                                                    aria-hidden="true"></i> Submitted</a>
                                        @endif
                                    @else
                                        <a href="javascript:;" data-toggle="modal" class="btn btn-sm btn-success"
                                            data-backdrop="false" data-target="#submitApplication{{ $key }}"
                                            title="Submit this application"><i class="fa fa-send"></i> Submit</a>
                                    @endif
                                </td>
                                {{-- <td>
                                        <a href="javascript:;" data-toggle="modal" class="btn btn-sm btn-danger"
                                            data-backdrop="false" data-target="#deleteApplication{{ $key }}"
                                            title="Delete this application"><i class="fa fa-trash"></i></a>
                                        <a href="javascript:;" data-toggle="modal" class="btn btn-sm btn-warning"
                                            data-backdrop="false" data-target="#editApplication{{ $key }}"
                                            title="Edit this application"><i class="fa fa-edit"></i></a>

                                </td> --}}

                            </tr>

                            <!-- Submit Modal -->
                            <div class="modal fade text-left d-print-none" id="submitApplication{{ $key }}"
                                tabindex="-1" role="dialog" aria-labelledby="myModalLabel12" aria-hidden="true">
                                <div class="modal-dialog" role="document">
                                    <div class="modal-content">
                                        <div class="modal-header bg-gray white">
                                            <h4 class="modal-title" id="myModalLabel12"><i class="fa fa-exclamation"></i>
                                                Confirm
                                            </h4>
                                            <button type="button" class="close" data-dismiss="modal"
                                                aria-label="Close">
                                                <span aria-hidden="true">&times;</span>
                                            </button>
                                        </div>
                                        <div align="center" class="modal-body">
                                            <div class="text-center"> {{ __('Submit Application Now!!!') }} </div>
                                            <h5><i class="fa fa-user"></i>
                                                {{ __('Are you sure you want to submit this application?') }} </h5>
                                        </div>
                                        <div class="modal-footer">
                                            <button type="button" class="btn grey btn-outline-secondary"
                                                data-dismiss="modal">{{ __('Close') }}</button>
                                            <a href="{{ route('submitApplication', [$value->roasterID]) }}"
                                                class="btn btn-success">{{ __('Submit') }}</a>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <!--end Modal-->

                            <!-- Delete Modal -->
                            <div class="modal fade text-left d-print-none" id="deleteApplication{{ $key }}"
                                tabindex="-1" role="dialog" aria-labelledby="myModalLabel12" aria-hidden="true">
                                <div class="modal-dialog" role="document">
                                    <div class="modal-content">
                                        <div class="modal-header bg-danger white">
                                            <h4 class="modal-title" id="myModalLabel12"><i class="fa fa-trash"></i>
                                                Confirm
                                            </h4>
                                            <button type="button" class="close" data-dismiss="modal"
                                                aria-label="Close">
                                                <span aria-hidden="true">&times;</span>
                                            </button>
                                        </div>
                                        <div align="center" class="modal-body">
                                            <div class="text-center text-danger"> {{ __('Delete Application Now!!!') }}
                                            </div>
                                            <h5><i class="fa fa-user"></i>
                                                {{ __('Are you sure you want to delete this application?') }} </h5>
                                        </div>
                                        <div class="modal-footer">
                                            <button type="button" class="btn grey btn-outline-secondary"
                                                data-dismiss="modal">{{ __('Close') }}</button>
                                            <a href="{{ route('deleteStaffRoasterApplication', [$value->roasterID]) }}"
                                                class="btn btn-danger">{{ __('Delete') }}</a>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <!--end Modal-->

                            <!-- Edit Modal -->
                            <div class="modal fade text-left d-print-none" id="editApplication{{ $key }}"
                                tabindex="-1" role="dialog" aria-labelledby="myModalLabel12" aria-hidden="true">
                                <div class="modal-dialog" role="document">
                                    <div class="modal-content">
                                        <div class="modal-header bg-gray white">
                                            <h4 class="modal-title" id="myModalLabel12"><i class="fa fa-edit"></i>
                                                Edit </h4>
                                            <button type="button" class="close" data-dismiss="modal"
                                                aria-label="Close">
                                                <span aria-hidden="true">&times;</span>
                                            </button>
                                        </div>
                                        <form method="post" action="{{ route('updateStaffLeaveApplication') }}">
                                            @csrf
                                            <div align="left" class="modal-body">

                                                <div class="row">
                                                    <div class="col-md-12">
                                                        <div class="col-md-6">
                                                            <div class="form-group">
                                                                {{-- <label for="totalLeaveDays">Total Annual Leave Working Days</label>
                                                                    <div class="form-control">{{ isset($getLeaveDays) ? $getLeaveDays : 0 }}</div> --}}
                                                                <input type="hidden" name="getleaveDays"
                                                                    value="{{ isset($getLeaveDays) ? $getLeaveDays : 0 }}">
                                                            </div>
                                                        </div>
                                                        <div class="col-md-6">
                                                            <div class="form-group">
                                                                {{-- <label for="totalLeaveDays">Leave Working Days Used</label>
                                                                    <div class="form-control">{{ isset($leaveUsed) ? $leaveUsed : 0 }}</div> --}}
                                                                <input type="hidden" name="leaveUsed"
                                                                    value="{{ isset($leaveUsed) ? $leaveUsed : 0 }}">
                                                            </div>
                                                        </div>
                                                    </div>

                                                    <div class="col-md-12">
                                                        <div class="col-md-6">
                                                            <div class="form-group">
                                                                <label for="leaveDays">Enter Leave Working Day(s) <span
                                                                        class="text-danger">*</span></label>
                                                                <input type="number" class="form-control" name="leaveDays"
                                                                    value="{{ $value->leaveDays }}">
                                                            </div>
                                                        </div>
                                                        <div class="col-md-6">
                                                            <div class="form-group">
                                                                <label for="startDate">Start Date <span
                                                                        class="text-danger">*</span> </label>
                                                                <input type="date" required class="form-control"
                                                                    name="startDate" value="{{ $value->startDate }}">
                                                            </div>
                                                        </div>

                                                    </div>

                                                    <div class="col-md-12">
                                                        <div class="col-md-6">
                                                            <div class="form-group">
                                                                <label for="homeAddress">Home Address <span
                                                                        class="text-grey">(Optional)</span> </label>
                                                                <textarea class="form-control" name="homeAddress">{{ $value->homeAddress }}</textarea>
                                                            </div>
                                                        </div>
                                                        <div class="col-md-6">
                                                            <div class="form-group">
                                                                <label for="description">Description <span
                                                                        class="text-danger">*</span></label>
                                                                <textarea class="form-control" required name="description">{{ $value->description }}</textarea>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <hr />
                                                </div>
                                            </div>
                                            <div class="modal-footer">
                                                <input type="hidden" name="recordID" value="{{ $value->roasterID }}" />
                                                <button type="button" class="btn btn-outline-secondary"
                                                    data-dismiss="modal">{{ __('Close') }}</button>
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
            <div>{{ $getRecord->links() }}</div>
        @endsection

        @push('select2')
            {{-- Script for select2 https://select2.org/getting-started/installation --}}
            <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
            <script>
                $(document).ready(function() {
                    $('.pickStaffDetail').select2({
                        width: 'element' // need to override the changed default
                    });
                });
            </script>
        @endpush
