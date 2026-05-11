@extends('layouts.layout')
@section('pageTitle')
    Leave Creation
@endsection

@section('content')
    <div class="panel panel-success">
        <div class="panel-heading with-border hidden-print">
            <h3 class="panel-title">@yield('pageTitle') <span id='processing'></span></h3>
        </div>

        <div class="panel-body">
            <div>
                {{-- @include('hr.Share.message') --}}
                {{-- <form action="{{url('saveLeave/leavetype')}}" method="POST">
                    {{ csrf_field() }}
                    <!--hidden field for updating record-->
                    <div style='padding: 10px 30px;'>
                        <div class='row'>
                            <div class='col-md-4'>
                        	    <div class="col-md-auto">
                                    <label class="">Leave Type: </label>
                                </div>
                                <input type="text" class="form-control" name="leave"/>
                        	</div>
                            <div class='col-md-4'>
                        	    <div class="col-md-auto">
                                    <label class="">Days: </label>
                                </div>
                                <input type="text" class="form-control" name="days"/>
                        	</div>
                            <div class='col-md-4' style='padding-top: 25px;'>
                                <input type="submit" class="btn btn-success" name="btnSave" value='Add New Leave'>
                            </div>
                        </div>
                    </div>
                </form> --}}

                <div class="panel panel-success">
                    <div class="panel-heading">
                        <h4 class="panel-title">Add New Leave Type</h4>
                    </div>

                    <div class="panel-body">
                        <form action="{{ url('saveLeave/leavetype') }}" method="POST">
                            {{ csrf_field() }}

                            <div class="row">

                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label>Leave Type:</label>
                                        <input type="text" class="form-control" name="leave"
                                            placeholder="Enter leave type">
                                    </div>
                                </div>

                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label>Days:</label>
                                        <input type="text" class="form-control" name="days"
                                            placeholder="Number of days">
                                    </div>
                                </div>

                                <div class="col-md-4">
                                    <div class="form-group" style="padding-top: 25px;">
                                        <button type="submit" class="btn btn-success btn-block">
                                            Add New Leave
                                        </button>
                                    </div>
                                </div>

                            </div>
                        </form>
                    </div>
                </div>

                {{-- <div class="table-responsive" style="font-size: 12px; padding-left: 30px; padding-top: 16px; width: 80rem">
                    <div class='col-md-auto'>
                        <table id="mytable" class="table table-bordered table-striped table-highlight col-md-9">
                            <thead>
                                <tr bgcolor="#c7c7c7">
                                    <th>S/N</th>
                                    <th>TYPE</th>
                                    <th>NUMBER OF DAYS</th>
                                    <th>ACTION</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($getleave as $i=>$list)
                                    <tr>
                                        <td class='col-md-auto'>{{ 1 + $i }}</td>
                                        <td class='col-md-4 text-capitalize'>{{ $list->leaveType }}</td>
                                        <td class='col-md-4 text-capitalize'>{{ $list->days ?? '' }}</td>
                                        <td class='col-md-auto'>
                                            <button type="button" class="btn btn-primary btn-sm" data-toggle="modal"
                                                data-target="#updateModal{{ $list->id }}"
                                                style="margin-bottom: 3px">Edit</button>
                                            <button type="button" class="btn btn-danger btn-sm"
                                                onclick="showDeleteModal('{{ $list->id }}', '{{ $list->leaveType }}')">Delete</button>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="4" align="center">No Record Found!</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div> --}}
                <div class="panel panel-success" style="  margin-top: 16px; font-size: 12px;">
                    <div class="panel-heading">
                        <h4 class="panel-title">Leave Types</h4>
                    </div>

                    <div class="panel-body">
                        <div class="table-responsive">
                            <table id="mytable" class="table table-bordered table-striped table-highlight">
                                <thead>
                                    <tr bgcolor="#c7c7c7">
                                        <th>S/N</th>
                                        <th>TYPE</th>
                                        <th>NUMBER OF DAYS</th>
                                        <th>ACTION</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($getleave as $i=>$list)
                                        <tr>
                                            <td>{{ 1 + $i }}</td>
                                            <td class="text-capitalize">{{ $list->leaveType }}</td>
                                            <td class="text-capitalize">{{ $list->days ?? '' }}</td>
                                            <td>
                                                <button type="button" class="btn btn-primary btn-sm" data-toggle="modal"
                                                    data-target="#updateModal{{ $list->id }}">
                                                    Edit
                                                </button>

                                                <button type="button" class="btn btn-danger btn-sm"
                                                    onclick="showDeleteModal('{{ $list->id }}', '{{ $list->leaveType }}')">
                                                    Delete
                                                </button>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="4" align="center">No Record Found!</td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- EDIT MODALS PLACED OUTSIDE THE MAIN CONTENT -->
    @foreach ($getleave as $list)
        <div class="modal fade" id="updateModal{{ $list->id }}" tabindex="-1" role="dialog"
            aria-labelledby="updateModalLabel{{ $list->id }}" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title fw-bold">Edit Record</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <form method="post" action="{{ url('/update/leavetype') }}" role="form">
                        {{ csrf_field() }}
                        <div class="modal-body">
                            <input type="hidden" value="{{ $list->id }}" name='leaveId' />
                            {{-- <div class="form-group">
                           <label class="col-form-label">Leave Type:</label>
                           <input type="text" class="form-control" name="leave" id="leave{{$list->id}}" value="{{ $list->leaveType }}" required/>
                       </div> --}}

                            <div class="row">
                                <div class='col-md-4'>
                                    <div class="col-md-auto">
                                        <label class="">Leave Type: </label>
                                    </div>
                                    <input type="text" class="form-control" name="leave" id="leave{{ $list->id }}"
                                        value="{{ $list->leaveType }}" required />
                                </div>
                                <div class='col-md-4'>
                                    <div class="col-md-auto">
                                        <label class="">Days: </label>
                                    </div>
                                    <input type="text" class="form-control" name="days"
                                        value="{{ $list->days }}" />
                                </div>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                            <button type="submit" class="btn btn-primary">Save changes</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    @endforeach

    <!-- DELETE CONFIRMATION MODAL -->
    <div class="modal fade" id="deleteModal" tabindex="-1" role="dialog" aria-labelledby="deleteModalLabel"
        aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered" role="document">
            <div class="modal-content">
                <div class="modal-header bg-danger">
                    <h5 class="modal-title text-white">Confirm Delete</h5>
                    <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <p>You are about to delete this leave type. This action will permanently remove the record from the
                        system. Are you sure you want to proceed?</p>
                    <div class="alert alert-warning">
                        <strong>Leave Type: <span id="deleteLeaveName" class="text-danger"></span></strong>
                    </div>
                    <p class="text-muted"><small>This action cannot be undone.</small></p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                    <a id="deleteConfirmBtn" class="btn btn-danger">Delete Leave Type</a>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('scripts')
    <script src="{{ asset('assets/js/jquery.slimscroll.min.js') }}"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery-confirm/3.3.2/jquery-confirm.min.js"></script>

    <script type="text/javascript">
        $(document).ready(function() {
            $('#input-tags2').selectize({
                plugins: ['restore_on_backspace'],
                delimiter: ',',
                persist: false,
                create: function(input) {
                    return {
                        value: input,
                        text: input
                    }
                }
            });
        });

        function myFunction(val) {
            alert(val);
        }

        function showDeleteModal(leaveId, leaveName) {
            // Set the leave name in the modal
            document.getElementById('deleteLeaveName').textContent = leaveName;

            // Set the delete URL
            var deleteUrl = '{{ url('/leave/delete') }}/' + leaveId;
            document.getElementById('deleteConfirmBtn').href = deleteUrl;

            // Show the modal
            $('#deleteModal').modal('show');
        }

        // Optional: Add smooth handling for delete action
        $(document).ready(function() {
            $('#deleteConfirmBtn').on('click', function(e) {
                e.preventDefault();
                var deleteUrl = $(this).attr('href');

                // Optional: Add loading state
                $(this).html('<i class="fa fa-spinner fa-spin"></i> Deleting...');
                $(this).prop('disabled', true);

                // Perform the delete action
                window.location.href = deleteUrl;
            });
        });
    </script>
    @if (session('message'))
        <script>
            Swal.fire({
                toast: true,
                position: "top-end",
                icon: "success",
                title: "{{ session('message') }}",
                showConfirmButton: false,
                timer: 2500
            });
        </script>
    @endif

    {{-- @if (session('message'))
        <script>
            Swal.fire({
                toast: true,
                position: "top-end",
                icon: "error",
                title: "{{ session('message') }}",
                showConfirmButton: false,
                timer: 2500
            });
        </script>
    @endif --}}
@endsection

@section('styles')
    <style>
        .modal-header.bg-danger .close {
            opacity: 1;
            text-shadow: none;
        }

        .modal-header.bg-danger .close:hover {
            opacity: 0.8;
        }
    </style>
@endsection
