@extends('layouts.layout')
@section('pageTitle')
    LOA Application
@endsection

@section('content')
    <div class="panel panel-success">
        {{-- <div class="panel-heading with-border hidden-print">
            <h3 class="panel-title">@yield('pageTitle') <span id='processing'></span></h3>



            <button class="btn btn-success" data-toggle="modal" data-target="#leaveModal"
                data-id="{{ $employee->ID ?? Auth::user()->id }}"
                data-name="{{ ($employee->surname ?? Auth::user()->surname) . ' ' . ($employee->first_name ?? Auth::user()->first_name) . ' ' . ($employee->othernames ?? Auth::user()->othernames) }}">
                Add Leave Application
            </button>
        </div> --}}

        <div class="panel-heading hidden-print clearfix">
            <h3 class="panel-title pull-left">
                @yield('pageTitle') <span id="processing"></span>
            </h3>

            <button class="btn btn-success pull-right" data-toggle="modal" data-target="#leaveModal"
                data-id="{{ $employee->ID ?? Auth::user()->id }}"
                data-name="{{ ($employee->surname ?? Auth::user()->surname) . ' ' . ($employee->first_name ?? Auth::user()->first_name) . ' ' . ($employee->othernames ?? Auth::user()->othernames) }}">
                Add Leave of Absence
            </button>
        </div>



        <div class="panel-body">
            <div>
                {{-- @include('hr.Share.message') --}}


                {{-- <div class="panel panel-success no-print">
                    <div class="panel-heading">
                        <h3 class="panel-title">Apply Leave Of Absense </h3>
                    </div>

                    <div class="panel-body">
                        <form action="{{ url('saveapply/loa') }}" method="POST">
                            {{ csrf_field() }}
                            <input type="hidden" name="employee_id" value="{{ $employee->ID ?? Auth::user()->id }}">

                            <div class="row">





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
                </div> --}}



                <div class="col-md-12" style="padding: 5px;">
                    <div class="panel panel-success" style="border-radius: 6px; box-shadow: 0 2px 8px rgba(0,0,0,0.1);">



                        <div class="panel-heading clearfix">
                            <h4 class="panel-title pull-left" style="font-size:16px; font-weight:bold;">
                                Leave of Absence Records
                            </h4>

                            <button onclick="printPage()" class="btn btn-success btn-sm pull-right">
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
                                            {{-- <th>Leave Type</th> --}}
                                            {{-- <th>Reason</th> --}}
                                            <th>Duration</th>
                                            <th>Date Applied</th>
                                            <th>Status</th>
                                            <th class="no-print">Action</th>
                                        </tr>
                                    </thead>

                                    <tbody id="loaTableBody">
                                        @include('hr.Leave._loa_rows')
                                    </tbody>
                                </table>
                            </div>
                        </div>

                    </div>
                </div>
            </div>
        </div>
    </div>


    {{-- Add Modal --}}

    {{-- <div class="modal fade" id="leaveModal" tabindex="-1" role="dialog">
        <div class="modal-dialog modal-md" role="document">

            <div class="modal-content">

                <!-- Modal Header -->
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                    <h4 class="modal-title">Apply Leave Of Absence</h4>
                </div>

                <!-- Modal Body -->
                <div class="modal-body">

                    <form action="{{ url('saveapply/loa') }}" method="POST">
                        {{ csrf_field() }}

                        <input type="hidden" name="employee_id" value="{{ $employee->ID ?? Auth::user()->id }}">

                        <div class="row">

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

                        </div>

                        <!-- Submit -->
                        <div class="modal-footer">
                            <button type="submit" class="btn btn-success">
                                Submit Leave Application
                            </button>
                            <button type="button" class="btn btn-default" data-dismiss="modal">
                                Close
                            </button>
                        </div>

                    </form>

                </div>

            </div>
        </div>
    </div> --}}

    <div class="modal fade" id="leaveModal" tabindex="-1">
        <div class="modal-dialog modal-sm">

            <div class="modal-content">

                {{-- <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                    <h4 class="modal-title">Apply Leave Of Absence</h4>
                </div> --}}





                <div class="modal-body">



                    <div class="panel panel-success">
                        <div class="panel-heading">
                            <h3 class="panel-title">
                                Apply Leave Of Absence
                            </h3>
                        </div>

                        <div class="panel-body">


                            <form id="leaveForm">

                                {{ csrf_field() }}



                                <div class="form-group">
                                    <label>Employee</label>

                                    @if ($isSuperAdmin || $isAdminStaff)
                                        <!-- SUPER ADMIN / ADMIN STAFF MODE -->
                                        <select class="form-control" name="employee_id" id="employee_id" required>
                                            <option value="">-- Select Employee --</option>
                                            @foreach ($getEnployee as $emp)
                                                <option value="{{ $emp->ID }}" {{ ($employee && $employee->ID == $emp->ID) ? 'selected' : '' }}>
                                                    {{ $emp->surname }} {{ $emp->first_name }} {{ $emp->othernames }}
                                                </option>
                                            @endforeach
                                        </select>
                                    @else
                                        <!-- STAFF / HOD -->
                                        @if ($employee)
                                            <input type="hidden" name="employee_id" id="employee_id"
                                                value="{{ $employee->ID }}">
                                            <input type="text" class="form-control" readonly
                                                value="{{ $employee->surname }} {{ $employee->first_name }} {{ $employee->othernames }}">
                                        @else
                                            <input type="text" class="form-control" readonly value="No employee profile found">
                                        @endif
                                    @endif
                                </div>

                                <div class="row">

                                    <div class="col-md-12">
                                        <label>Start Date</label>
                                        <input type="date" name="start_date" class="form-control" required>
                                    </div>

                                    <div class="col-md-12">
                                        <label>End Date</label>
                                        <input type="date" name="end_date" class="form-control" required>
                                    </div>

                                    <div class="col-md-12">
                                        <label>Reason</label>
                                        <textarea name="leave_reason" class="form-control" required></textarea>
                                    </div>

                                </div>

                                <br>

                                <button type="submit" class="btn btn-success btn-block">
                                    Submit Leave Of Absence
                                </button>

                            </form>



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
    <script src="{{ asset('assets/js/jquery-ui.min.js') }}"></script>
    {{-- <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

<script src="{{ asset('assets/js/jquery.slimscroll.min.js') }}"></script> --}}
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery-confirm/3.3.2/jquery-confirm.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>


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

    {{-- <script>
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
    </script> --}}


    <script>
        function confirmAction(url, actionType) {
            Swal.fire({
                title: "Are you sure?",
                text: "Do you want to " + actionType + " this leave request?",
                icon: "warning",
                showCancelButton: true,
                confirmButtonText: "Yes, " + actionType,
                cancelButtonText: "Cancel"
            }).then((result) => {
                if (result.isConfirmed) {
                    window.location.href = url;
                }
            });
        }
    </script>


    {{-- <script>
        // $('#leaveForm').on('submit', function(e) {
        //     e.preventDefault();

        //     $.ajax({
        //         url: "{{ url('saveapply/loa') }}",
        //         method: "POST",
        //         data: $(this).serialize(),
        //         success: function(response) {

        //             $('#leaveModal').modal('hide');

        //             Swal.fire({
        //                 toast: true,
        //                 position: "top-end",
        //                 icon: "success",
        //                 title: "Leave applied successfully!",
        //                 showConfirmButton: false,
        //                 timer: 2500
        //             });

        //             $('#leaveForm')[0].reset();
        //         },
        //         error: function(xhr) {

        //             Swal.fire({
        //                 toast: true,
        //                 position: "top-end",
        //                 icon: "error",
        //                 title: "Something went wrong!",
        //                 showConfirmButton: false,
        //                 timer: 3000
        //             });

        //         }
        //     });
        // });
        function refreshLoaTable() {
            $.ajax({
                url: "{{ route('loa.list') }}",
                type: "GET",
                success: function(data) {
                    $("#loaTableBody").html(data);
                }
            });
        }
        $('#leaveForm').on('submit', function(e) {
            e.preventDefault();

            $.ajax({
                url: "{{ url('saveapply/loa') }}",
                method: "POST",
                data: $(this).serialize(),
                success: function(response) {

                    $('#leaveModal').modal('hide');

                    Swal.fire({
                        toast: true,
                        position: "top-end",
                        icon: "success",
                        title: "Leave of Absence Submitted!",
                        timer: 2000,
                        showConfirmButton: false
                    });

                    $('#leaveForm')[0].reset();

                    // 🔥 FORCE REFRESH TABLE
                    refreshLoaTable();
                },
                error: function(xhr) {
                    Swal.fire({
                        toast: true,
                        position: "top-end",
                        icon: "error",
                        title: "Submission failed!",
                        timer: 2000,
                        showConfirmButton: false
                    });
                }
            });
        });
    </script> --}}

    {{-- <script>
        $(document).ready(function() {

            function refreshLoaTable() {
                $.ajax({
                    url: "{{ route('loa.list') }}",
                    type: "GET",
                    success: function(data) {
                        $("#loaTableBody").html(data);
                    }
                });
            }

            $('#leaveForm').on('submit', function(e) {
                e.preventDefault();

                $.ajax({
                    url: "{{ url('saveapply/loa') }}",
                    method: "POST",
                    data: $(this).serialize(),
                    success: function(response) {

                        $('#leaveModal').modal('hide');

                        Swal.fire({
                            toast: true,
                            position: "top-end",
                            icon: "success",
                            title: "Leave of Absence Submitted!",
                            timer: 2000,
                            showConfirmButton: false
                        });

                        $('#leaveForm')[0].reset();

                        refreshLoaTable();
                    },
                    error: function() {
                        Swal.fire({
                            toast: true,
                            position: "top-end",
                            icon: "error",
                            title: "Submission failed!",
                            timer: 2000,
                            showConfirmButton: false
                        });
                    }
                });
            });

        });
    </script> --}}

    <script>
        // $(document).ready(function() {

        //     function refreshLoaTable() {
        //         $.ajax({
        //             url: "{{ route('loa.list') }}",
        //             type: "GET",
        //             success: function(data) {
        //                 $("#loaTableBody").html(data);
        //             }
        //         });
        //     }

        //     $('#leaveForm').on('submit', function(e) {
        //         e.preventDefault();

        //         $.ajax({
        //             url: "{{ url('saveapply/loa') }}",
        //             method: "POST",
        //             data: $(this).serialize(),
        //             success: function(response) {

        //                 // Reset form immediately
        //                 $('#leaveForm')[0].reset();

        //                 // Close modal first
        //                 $('#leaveModal').modal('hide');

        //                 // Refresh table immediately (no delay)
        //                 refreshLoaTable();
        //             },
        //             error: function() {
        //                 Swal.fire({
        //                     toast: true,
        //                     position: "top-end",
        //                     icon: "error",
        //                     title: "Submission failed!",
        //                     timer: 2000,
        //                     showConfirmButton: false
        //                 });
        //             }
        //         });
        //     });

        //     // 🔥 Show SUCCESS notification *after* modal has fully closed
        //     $('#leaveModal').on('hidden.bs.modal', function() {
        //         Swal.fire({
        //             toast: true,
        //             position: "top-end",
        //             icon: "success",
        //             title: "Leave of Absence Submitted!",
        //             timer: 2000,
        //             showConfirmButton: false
        //         });
        //     });

        // });

        // $('#leaveForm').on('submit', function(e) {
        //     e.preventDefault();

        //     // Close modal immediately
        //     $('#leaveModal').modal('hide');

        //     // Show success immediately
        //     Swal.fire({
        //         toast: true,
        //         position: "top-end",
        //         icon: "success",
        //         title: "Submitting Leave...",
        //         timer: 1500,
        //         showConfirmButton: false
        //     });

        //     // Run AJAX silently in background
        //     $.ajax({
        //         url: "{{ url('saveapply/loa') }}",
        //         method: "POST",
        //         data: $(this).serialize(),
        //         success: function(response) {
        //             refreshLoaTable();

        //             Swal.fire({
        //                 toast: true,
        //                 position: "top-end",
        //                 icon: "success",
        //                 title: "Leave Submitted!",
        //                 timer: 2000,
        //                 showConfirmButton: false
        //             });
        //         },
        //         error: function() {
        //             Swal.fire({
        //                 toast: true,
        //                 position: "top-end",
        //                 icon: "error",
        //                 title: "Error saving leave",
        //                 timer: 2000,
        //                 showConfirmButton: false
        //             });
        //         }
        //     });

        //     $('#leaveForm')[0].reset();
        // });
    </script>

    <script>
        function refreshLoaTable() {
            $.ajax({
                url: "{{ route('loa.list') }}",
                type: "GET",
                success: function(data) {
                    $("#loaTableBody").html(data);
                }
            });
        }

        $('#leaveForm').on('submit', function(e) {
            e.preventDefault();

            // Close modal immediately
            $('#leaveModal').modal('hide');

            // Show success immediately
            Swal.fire({
                toast: true,
                position: "top-end",
                icon: "success",
                title: "Submitting Leave...",
                timer: 1500,
                showConfirmButton: false
            });

            // Run AJAX silently in background
            $.ajax({
                url: "{{ url('saveapply/loa') }}",
                method: "POST",
                data: $(this).serialize(),
                success: function(response) {

                    // NOW THIS WILL WORK
                    refreshLoaTable();

                    Swal.fire({
                        toast: true,
                        position: "top-end",
                        icon: "success",
                        title: "Leave Submitted!",
                        timer: 2000,
                        showConfirmButton: false
                    });
                },
                error: function() {
                    Swal.fire({
                        toast: true,
                        position: "top-end",
                        icon: "error",
                        title: "Error saving leave",
                        timer: 2000,
                        showConfirmButton: false
                    });
                }
            });

            $('#leaveForm')[0].reset();
        });
    </script>



    @if (session('message'))
        <script>
            Swal.fire({
                toast: true,
                position: 'top-end',
                icon: 'success',
                title: "{{ session('message') }}",
                showConfirmButton: false,
                timer: 3000
            });
        </script>
    @endif
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

        /* Modern unified input and select styles matching premium aesthetics */
        .form-control {
            height: 48px;
            padding: 10px 16px;
            font-size: 14px;
            border-radius: 8px;
            border: 2px solid #e2e8f0;
            background-color: #f8fafc;
            color: #0f172a;
            transition: all 0.2s ease-in-out;
            box-sizing: border-box;
            box-shadow: none;
        }
        .form-control:focus {
            border-color: #3b82f6;
            background-color: #ffffff;
            box-shadow: 0 0 0 4px rgba(59, 130, 246, 0.15) !important;
            outline: none;
        }
        textarea.form-control {
            height: auto;
            min-height: 90px;
            resize: vertical;
        }
        .form-control[readonly] {
            background-color: #f1f5f9;
            opacity: 0.7;
            cursor: not-allowed;
        }
    </style>
@endsection
