@extends('layouts.layout')
@section('pageTitle')
    Leave Application
@endsection

@section('content')
    <div class="panel panel-primary">
        <div class="panel-heading with-border hidden-print">
            <h3 class="panel-title">@yield('pageTitle') <span id='processing'></span></h3>
        </div>

        <div class="panel-body">
            <div>
                @include('hr.Share.message')


                <div class="panel panel-primary no-print">
                    <div class="panel-heading">
                        <h3 class="panel-title">Apply for Leave</h3>
                    </div>

                    <div class="panel-body">
                        <form action="{{ url('saveapply/leave') }}" method="POST">
                            {{ csrf_field() }}

                            <div class="row">

                                <!-- Leave Type -->
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label>Leave Type:</label>
                                        <select name="leave_type" class="form-control" required>
                                            <option value="">-- Select Leave Type --</option>
                                            @foreach ($getleave as $leave)
                                                <option value="{{ $leave->id }}">{{ $leave->leaveType }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>

                                <!-- Employee -->
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label>Employee:</label>
                                        <select name="employee_id" class="form-control" required>
                                            <option value="">-- Select Employee --</option>
                                            @foreach ($getEnployee as $emp)
                                                <option value="{{ $emp->ID }}">
                                                    {{ $emp->surname }} {{ $emp->first_name }} {{ $emp->othernames }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>

                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label>Remaining Leave Days:</label>
                                        <input type="text" name="remaining_days" class="form-control" readonly>
                                    </div>
                                </div>



                                <!-- Start Date -->
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label>Start Date:</label>
                                        <input type="date" name="start_date" class="form-control" required>
                                    </div>
                                </div>

                                <!-- End Date -->
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label>End Date:</label>
                                        <input type="date" name="end_date" class="form-control" required>
                                    </div>
                                </div>

                                <!-- Leave Reason -->
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label>Leave Reason:</label>
                                        <textarea name="leave_reason" class="form-control" rows="3" required></textarea>
                                    </div>
                                </div>

                                <!-- Submit Button -->
                                <div class="col-md-3">
                                    <div class="form-group" style="margin-top: 25px;">
                                        <button type="submit" class="btn btn-success btn-block">
                                            Submit Leave Application
                                        </button>
                                    </div>
                                </div>

                            </div>

                        </form>
                    </div>
                </div>



                <div class="col-md-12" style="padding: 5px;">
                    <div class="panel panel-primary" style="border-radius: 6px; box-shadow: 0 2px 8px rgba(0,0,0,0.1);">

                        <!-- Card Header -->
                        {{-- <div class="panel-heading"
                            style=" padding: 15px; border-top-left-radius: 6px; border-top-right-radius: 6px;">
                            <h4 class="panel-title" style="font-size: 16px; font-weight: bold;">
                                Leave Records
                            </h4>
                            <button onclick="printPage()" class="btn btn-success btn-sm">
                                Print
                            </button>
                        </div> --}}

                        <div class="panel-heading clearfix">
                            <h4 class="panel-title pull-left" style="font-size:16px; font-weight:bold;">
                                Leave Records
                            </h4>

                            <button onclick="printPage()" class="btn btn-primary btn-sm pull-right">
                                Print / Download PDF
                            </button>
                        </div>

                        <!-- Card Body -->
                        <div class="panel-body" id="printArea">
                            <div class="table-responsive">
                                <table id="mytable" class="table table-bordered table-striped table-hover">
                                    <thead>
                                        <tr bgcolor="#eaeaea">
                                            <th>S/N</th>
                                            <th>Staff Name</th>
                                            <th>Department</th>
                                            <th>Leave Type</th>
                                            {{-- <th>Reason</th> --}}
                                            <th>Duration</th>
                                            <th>Date Applied</th>
                                            <th>Status</th>
                                            <th class="no-print">Action</th>
                                        </tr>
                                    </thead>

                                    <tbody>
                                        @forelse($getleaveRecord as $i => $list)
                                            <tr>
                                                <td>{{ $i + 1 }}</td>

                                                <!-- Staff Name -->
                                                <td class="text-capitalize">
                                                    {{ $list->surname }} {{ $list->first_name }} {{ $list->othernames }}
                                                </td>

                                                <!-- Department -->
                                                <td>{{ $list->department }}</td>

                                                <!-- Leave Type -->
                                                <td>{{ $list->leaveType }}</td>

                                                <!-- Leave Reason -->
                                                {{-- <td>{{ $list->reason_of_leave }}</td> --}}

                                                <!-- Duration -->
                                                <td>
                                                    @php
                                                        $days =
                                                            \Carbon\Carbon::parse($list->start_date)->diffInDays(
                                                                \Carbon\Carbon::parse($list->end_date),
                                                            ) + 1;
                                                    @endphp
                                                    {{ $days }} days
                                                </td>

                                                <!-- Date Applied -->
                                                <td>{{ \Carbon\Carbon::parse($list->created_at)->format('d M, Y') }}</td>

                                                <!-- Status -->
                                                <td>
                                                    @if ($list->status == 1)
                                                        <span class="label label-success">Approved</span>
                                                    @elseif ($list->status == 2)
                                                        <span class="label label-danger">Rejected</span>
                                                    @else
                                                        <span class="label label-warning">Pending</span>
                                                    @endif
                                                </td>

                                                <!-- Action -->
                                                <td class="no-print">
                                                    {{-- <a href="{{ url('leave/view/' . $list->id) }}"
                                                            class="btn btn-info btn-sm">View</a> --}}

                                                    <a href="javascript:void(0)" class="btn btn-info btn-sm"
                                                        data-toggle="modal" data-target="#viewModal{{ $list->id }}">
                                                        View
                                                    </a>

                                                    <a href="{{ url('leave/approve/' . $list->id) }}"
                                                        class="btn btn-success btn-sm">Approve</a>

                                                    <a href="{{ url('leave/reject/' . $list->id) }}"
                                                        class="btn btn-danger btn-sm">Reject</a>
                                                </td>

                                                <!-- Action -->
                                                {{-- <td>
                                                    <a href="#" class="btn btn-info btn-sm">View</a>
                                                    <a href="#" class="btn btn-success btn-sm">Approve</a>
                                                    <a href="#" class="btn btn-danger btn-sm">Reject</a>
                                                </td> --}}
                                            </tr>

                                            <div id="viewModal{{ $list->id }}" class="modal fade" tabindex="-1"
                                                role="dialog">
                                                <div class="modal-dialog">
                                                    <div class="modal-content">

                                                        <!-- Modal Header -->
                                                        <div class="modal-header" style="background:#337ab7; color:#fff;">
                                                            <button type="button" class="close"
                                                                data-dismiss="modal">&times;</button>
                                                            <h4 class="modal-title">Leave Details</h4>
                                                        </div>

                                                        <!-- Modal Body -->
                                                        <div class="modal-body">

                                                            <p><strong>Staff Name:</strong>
                                                                {{ $list->surname }} {{ $list->first_name }}
                                                                {{ $list->othernames }}
                                                            </p>

                                                            <p><strong>Department:</strong> {{ $list->department }}</p>

                                                            <p><strong>Leave Type:</strong> {{ $list->leaveType }}</p>

                                                            {{-- <p><strong>Reason:</strong> {{ $list->reason_of_leave }}</p> --}}

                                                            <div class="panel panel-default">
                                                                <div class="panel-heading" style="font-weight: bold;">
                                                                    Leave Reason
                                                                </div>
                                                                <div class="panel-body">
                                                                    {{ $list->reason_of_leave }}
                                                                </div>
                                                            </div>

                                                            <p><strong>Start Date:</strong> {{ $list->start_date }}</p>

                                                            <p><strong>End Date:</strong> {{ $list->end_date }}</p>

                                                            <p>
                                                                <strong>Duration:</strong>
                                                                @php
                                                                    $days =
                                                                        \Carbon\Carbon::parse(
                                                                            $list->start_date,
                                                                        )->diffInDays(
                                                                            \Carbon\Carbon::parse($list->end_date),
                                                                        ) + 1;
                                                                @endphp
                                                                {{ $days }} days
                                                            </p>

                                                            <p><strong>Date Applied:</strong>
                                                                {{ \Carbon\Carbon::parse($list->created_at)->format('d M, Y') }}
                                                            </p>

                                                            <p>
                                                                <strong>Status:</strong>
                                                                @if ($list->status == 1)
                                                                    <span class="label label-success">Approved</span>
                                                                @elseif ($list->status == 2)
                                                                    <span class="label label-danger">Rejected</span>
                                                                @else
                                                                    <span class="label label-warning">Pending</span>
                                                                @endif
                                                            </p>

                                                        </div>

                                                        <!-- Modal Footer -->
                                                        <div class="modal-footer">
                                                            <button type="button" class="btn btn-default"
                                                                data-dismiss="modal">Close</button>
                                                        </div>

                                                    </div>
                                                </div>
                                            </div>

                                        @empty
                                            <tr>
                                                <td colspan="9" class="text-center">No Record Found!</td>
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
    </div>


    {{-- View modal --}}



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
                            <div class="form-group">
                                <label class="col-form-label">Leave Type:</label>
                                <input type="text" class="form-control" name="leave" id="leave{{ $list->id }}"
                                    value="{{ $list->leaveType }}" required />
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
    {{-- <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script> --}}
    script src="{{asset('assets/js/jquery-ui.min.js')}}"></script>
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
    <script>
        function printPage() {
            var oldTitle = document.title;
            document.title = "Leave Records"; // temporary print title
            window.print();
            document.title = oldTitle; // return original title
        }
    </script>

    <script>
        // $(document).ready(function() {

        //     $("select[name='employee_id'], input[name='start_date']").on("change", function() {

        //         let employee_id = $("select[name='employee_id']").val();
        //         let start_date = $("input[name='start_date']").val();

        //         if (employee_id !== "" && start_date !== "") {
        //             $.ajax({
        //                 url: "{{ url('/calculate-end-date') }}",
        //                 type: "GET",
        //                 data: {
        //                     employee_id: employee_id,
        //                     start_date: start_date
        //                 },
        //                 success: function(response) {
        //                     $("input[name='end_date']").val(response.end_date);
        //                 }
        //             });
        //         }

        //     });

        // });

        // $(document).ready(function() {

        //     $("select[name='employee_id'], select[name='leave_type'], input[name='start_date']").on("change",
        //         function() {

        //             let employee_id = $("select[name='employee_id']").val();
        //             let leave_type = $("select[name='leave_type']").val();
        //             let start_date = $("input[name='start_date']").val();

        //             if (employee_id !== "" && start_date !== "" && leave_type !== "") {
        //                 $.ajax({
        //                     url: "{{ url('/calculate-end-date') }}",
        //                     type: "GET",
        //                     data: {
        //                         employee_id: employee_id,
        //                         leave_type: leave_type,
        //                         start_date: start_date
        //                     },
        //                     success: function(response) {
        //                         $("input[name='end_date']").val(response.end_date);
        //                     }
        //                 });
        //             }

        //         });

        // });

        $(document).ready(function() {

            $("select[name='employee_id'], select[name='leave_type'], input[name='start_date']").on("change",
                function() {

                    let employee_id = $("select[name='employee_id']").val();
                    let leave_type = $("select[name='leave_type']").val();
                    let start_date = $("input[name='start_date']").val();

                    if (employee_id && leave_type && start_date) {
                        $.ajax({
                            url: "{{ url('/calculate-end-date') }}",
                            type: "GET",
                            data: {
                                employee_id: employee_id,
                                leave_type: leave_type,
                                start_date: start_date
                            },
                            success: function(response) {

                                if (response.remaining_days !== undefined) {
                                    $("input[name='remaining_days']").val(response.remaining_days);
                                }

                                if (response.end_date !== undefined) {
                                    $("input[name='end_date']").val(response.end_date);
                                }
                            }
                        });
                    }
                });

        });
    </script>
@endsection

@section('styles')
    <style>
        @media print {

            /* Hide buttons and unnecessary UI */
            .btn,
            .panel-heading,
            .no-print {
                display: none !important;
            }

            /* Make table clean */
            table {
                font-size: 12px !important;
            }

            body {
                margin: 0;
                padding: 0;
            }

            .panel {
                border: none !important;
                box-shadow: none !important;
            }
        }
    </style>
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
