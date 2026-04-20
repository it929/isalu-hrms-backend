@extends('layouts.layout')

@section('pageTitle')
    <strong>Department Setup</strong>
@endsection

@section('content')
    <style>
        /* ========= Department Setup Page Styling ========= */

        body {
            background-color: #f4f6f9;
            font-family: "Segoe UI", Roboto, Arial, sans-serif;
        }

        /* Card Container */
        .department-card {
            background: #fff;
            border-radius: 1rem;
            box-shadow: 0 3px 10px rgba(0, 0, 0, 0.08);
            margin-top: 20px;
            overflow: hidden;
            transition: all 0.3s ease;
        }

        .department-card:hover {
            box-shadow: 0 5px 20px rgba(0, 0, 0, 0.1);
        }

        /* Card Header */
        .department-card .card-header {
            /* background: linear-gradient(90deg, #007bff, #0056b3); */
            background: linear-gradient(90deg, #449d44, #337a33) !important;
            color: #fff;
            padding: 15px 20px;
            font-size: 1.1rem;
            font-weight: 600;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        /* Form Layout */
        .department-form {
            padding: 20px 25px;
        }

        .department-form label {
            font-weight: 600;
            color: #333;
            margin-bottom: 6px;
        }

        .department-form input[type="text"],
        .department-form select {
            border-radius: 6px;
            border: 1px solid #ced4da;
            padding: 10px 12px;
            height: 45px;
            transition: border-color 0.2s ease;
        }

        .department-form input[type="text"]:focus,
        .department-form select:focus {
            border-color: #007bff;
            box-shadow: 0 0 0 2px rgba(0, 123, 255, 0.15);
            outline: none;
        }

        .department-form button {
            margin-top: 28px;
            border-radius: 6px;
            font-weight: 600;
            padding: 10px 18px;
            transition: all 0.2s ease;
        }

        .department-form button i {
            margin-right: 6px;
        }

        /* Alerts */
        .alert {
            border-radius: 8px;
            font-weight: 500;
            margin-bottom: 15px;
        }

        .alert strong {
            font-weight: 600;
        }

        /* Table */
        .table-section {
            padding: 20px 25px;
        }

        .table-section table {
            border-collapse: collapse;
            width: 100%;
            border: 1px solid #dee2e6;
            font-size: 14px;
            background: #fff;
            border-radius: 6px;
            overflow: hidden;
        }

        .table-section th {
            background-color: #f1f3f5;
            font-weight: 600;
            text-transform: uppercase;
            font-size: 13px;
            color: #333;
        }

        .btn-success {
            background: linear-gradient(90deg, #449d44, #337a33) !important;
        }

        .table-section th,
        .table-section td {
            padding: 10px 12px;
            text-align: left;
            border-bottom: 1px solid #e9ecef;
        }

        .table-section tr:hover {
            background-color: #f9fafb;
        }

        /* Delete Button */
        .table-section .btn-delete {
            background: #dc3545;
            color: #fff;
            border: none;
            border-radius: 4px;
            padding: 5px 10px;
            font-size: 13px;
            transition: background 0.2s ease;
        }

        .table-section .btn-delete:hover {
            background: #c82333;
        }

        /* Pagination styling for non-Bootstrap environments */
        .pagination {
            display: inline-block;
            padding-left: 0;
            margin: 10px 0;
            border-radius: 4px;
        }

        .pagination li {
            display: inline;
        }

        .pagination li a,
        .pagination li span {
            color: #00a65a;
            padding: 6px 12px;
            text-decoration: none;
            border: 1px solid #ddd;
            margin-left: -1px;
        }

        .pagination li.active span {
            background-color: #00a65a;
            color: white;
            border-color: #00a65a;
        }

        .pagination li a:hover {
            background-color: #e9ecef;
        }

        /* Responsive Adjustments */
        @media (max-width: 768px) {
            .department-form {
                padding: 15px;
            }

            .table-section {
                padding: 15px;
            }

            .department-form button {
                width: 100%;
                margin-top: 10px;
            }
        }

        .panel {
            border-radius: 6px;
            box-shadow: 0 2px 6px rgba(0, 0, 0, 0.1);
        }

        .panel-heading {
            border-radius: 6px 6px 0 0 !important;
        }

        .table>thead>tr>th {
            background: #f1f1f1;
        }
    </style>




    <div style="padding-top: 25px;">
        <div class="panel "
            style="border-radius: 8px; background-color: #ecf0f5; box-shadow: 0 2px 8px rgba(0,0,0,0.1); border: none;">
            <div class="panel-heading" style="border-top-left-radius: 8px; border-top-right-radius: 8px;">
                <div class="row">
                    <div class="col-sm-6">
                        <h4 class="panel-title" style="margin: 0; color: #333;">
                            <i class="fa fa-building"></i>
                            @yield('pageTitle')
                        </h4>
                    </div>
                    <div class="col-sm-6 text-right">
                        <span id="processing" class="small"></span>
                    </div>
                </div>
            </div>

            <div class="panel-body"
                style="background-color: #f9f9f9; border-bottom-left-radius: 8px; border-bottom-right-radius: 8px;">

                <!-- Add Department Form -->
                <div class="box box-primary" style="border-radius: 6px; border: 1px solid #337ab7;">
                    <div class="box-header with-border"
                        style="background-color: #337ab7; color: #ecf0f5; border-top-left-radius: 6px; border-top-right-radius: 6px;">
                        <h4 class="box-title" style="margin: 0; line-height: 1.6;">
                            <i class="fa fa-plus-circle"></i> Add Department
                        </h4>
                    </div>

                    <div class="box-body" style="padding: 20px;">
                        <form method="post" id="thisform1" name="thisform1" class="form-horizontal">
                            {{ csrf_field() }}

                            @if ($CourtInfo->courtstatus == 1)
                                <div class="form-group">
                                    <label class="col-sm-2 control-label">Court</label>
                                    <div class="col-sm-10">
                                        <select name="court" id="court" class="form-control" required
                                            onchange="ReloadForm();">
                                            <option value="">-- Select Court --</option>
                                            @foreach ($CourtList as $b)
                                                <option value="{{ $b->id }}"
                                                    {{ $court == $b->id ? 'selected' : '' }}>
                                                    {{ $b->court_name }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                            @else
                                <input type="hidden" id="court" name="court" value="{{ $CourtInfo->courtid }}">
                            @endif

                            <div class="form-group">
                                <label class="col-sm-2 control-label">Department</label>
                                <div class="col-sm-8">
                                    <input type="text" name="department" id="department" class="form-control"
                                        value="{{ $department }}" placeholder="Enter new department name">
                                </div>
                                <div class="col-sm-2">
                                    <button type="submit" class="btn btn-success btn-block" name="add">
                                        <i class="fa fa-floppy-o"></i> Add
                                    </button>
                                </div>
                            </div>

                            <input id="delcode" type="hidden" name="delcode">
                        </form>
                    </div>
                </div>



                <div class="panel panel-primary" style="margin-top:20px;">


                    <div class="panel-heading">
                        <h3 class="panel-title">
                            <i class="glyphicon glyphicon-folder-open"></i> Department List
                        </h3>
                    </div>

                    <div class="panel-body">

                        <div class="table-responsive">
                            <table class="table table-hover table-bordered">
                                <thead style="background-color: #f7f7f7;">
                                    <tr>
                                        <th style="width: 60px;">S/N</th>
                                        <th>Department</th>
                                        <th style="width: 150px; text-align: center;">Action</th>
                                    </tr>
                                </thead>

                                <tbody>
                                    @php
                                        $serialNum =
                                            ($DepartmentList->currentPage() - 1) * $DepartmentList->perPage() + 1;
                                    @endphp

                                    @foreach ($DepartmentList as $b)
                                        <tr>
                                            <td>{{ $serialNum++ }}</td>
                                            <td>{{ $b->department }}</td>
                                            <td class="text-center">
                                                <button type="button" class="btn btn-xs btn-primary"
                                                    onclick="editDepartment('{{ $b->id }}', '{{ addslashes($b->department) }}')">
                                                    <i class="fa fa-edit"></i> Edit
                                                </button>

                                                <button type="button" class="btn btn-xs btn-danger"
                                                    onclick="DeletePromo('{{ $b->id }}', this)">
                                                    <i class="fa fa-trash"></i> Delete
                                                </button>
                                            </td>
                                        </tr>
                                    @endforeach

                                    @if (count($DepartmentList) == 0)
                                        <tr>
                                            <td colspan="3" class="text-center text-muted" style="padding: 20px;">
                                                No departments found.
                                            </td>
                                        </tr>
                                    @endif
                                </tbody>
                            </table>
                        </div>

                        <div class="text-right" style="margin-top: 15px;">
                            {{ $DepartmentList->links() }}
                        </div>

                    </div>
                </div>
            </div>
        </div>
    </div>



    <!-- ✅ Edit Department Modal -->
    <div id="editModal" class="modal fade" tabindex="-1" role="dialog" aria-labelledby="editModalLabel">
        <div class="modal-dialog" role="document">
            <div class="modal-content">

                <div class="modal-header "
                    style="color: #fff;background: linear-gradient(90deg, #449d44, #337a33) !important;">
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close" style="color:#fff;">
                        <span aria-hidden="true">&times;</span>
                    </button>
                    <h4 class="modal-title" id="editModalLabel">
                        <i class="fa fa-edit"></i> Edit Department
                    </h4>
                </div>

                <form method="POST" action="{{ url('/basic/section') }}" id="editDepartmentForm"
                    class="form-horizontal">
                    {{ csrf_field() }}
                    <div class="modal-body">
                        <div class="form-group">
                            <label for="edit_department" class="col-sm-3 control-label">Department</label>
                            <div class="col-sm-8">
                                <input type="text" class="form-control" id="edit_department" name="department"
                                    placeholder="Enter Department Name">
                                <input type="hidden" id="edit_id" name="editid">
                            </div>
                        </div>
                    </div>

                    <div class="modal-footer">
                        <button type="submit" class="btn btn-success">
                            <i class="fa fa-save"></i> Update
                        </button>
                        <button type="button" class="btn btn-default" data-dismiss="modal">
                            <i class="fa fa-times"></i> Close
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

@endsection

@section('styles')
    <link rel="stylesheet" href="{{ asset('assets/css/datepicker.min.css') }}">
    <style>
        body {
            background-color: #f8fafc;
        }

        .card {
            border-radius: 1rem;
        }
    </style>
@endsection


@section('scripts')
    <script src="{{ asset('assets/js/jquery-ui.min.js') }}"></script>

    {{-- SweetAlert2 CDN --}}
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>



    <script>
        function ReloadForm() {
            document.getElementById('thisform1').submit();
        }

        // 🔥 Delete confirmation using SweetAlert2
        // function DeletePromo(id) {
        //     Swal.fire({
        //         title: 'Are you sure?',
        //         text: "This will permanently delete the department.",
        //         icon: 'warning',
        //         showCancelButton: true,
        //         confirmButtonColor: '#3085d6',
        //         cancelButtonColor: '#d33',
        //         confirmButtonText: 'Yes, delete it!',
        //         cancelButtonText: 'Cancel'
        //     }).then((result) => {
        //         if (result.isConfirmed) {
        //             document.getElementById('delcode').value = id;
        //             document.getElementById('thisform1').submit();
        //         }
        //     });
        // }

        // Open Edit Modal
        function editDepartment(id, department) {
            $('#edit_id').val(id);
            $('#edit_department').val(department);
            $('#editModal').modal('show');
        }

        function DeletePromo(id, btn) {
            Swal.fire({
                title: 'Are you sure?',
                text: "You are about to delete this department.",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#28a745',
                cancelButtonColor: '#d33',
                confirmButtonText: 'Yes, delete it!',
                cancelButtonText: 'Cancel'
            }).then((result) => {
                if (result.isConfirmed) {
                    // Send AJAX POST to same route
                    $.ajax({
                        url: "{{ url('/basic/section') }}",
                        type: 'POST',
                        data: {
                            _token: '{{ csrf_token() }}',
                            delcode: id
                        },
                        // success: function() {
                        //     // Remove the deleted row
                        //     $(btn).closest('tr').fadeOut(400, function() {
                        //         $(this).remove();
                        //     });

                        //     // Show toast
                        //     Swal.fire({
                        //         toast: true,
                        //         position: 'top-end',
                        //         icon: 'success',
                        //         title: 'Department deleted successfully!',
                        //         showConfirmButton: false,
                        //         timer: 3000,
                        //         timerProgressBar: true
                        //     });
                        // },
                        success: function() {
                            // Remove the deleted row
                            $(btn).closest('tr').fadeOut(400, function() {
                                $(this).remove();
                                renumberTableRows(); // 🔥 Recalculate numbering
                            });

                            // Show toast
                            Swal.fire({
                                toast: true,
                                position: 'top-end',
                                icon: 'success',
                                title: 'Department deleted successfully!',
                                showConfirmButton: false,
                                timer: 3000,
                                timerProgressBar: true
                            });
                        },



                        error: function() {
                            Swal.fire({
                                icon: 'error',
                                title: 'Oops...',
                                text: 'Something went wrong while deleting.'
                            });
                        }
                    });
                }
            });
        }

        function renumberTableRows() {
            $('table tbody tr').each(function(index) {
                $(this).find('td:first').text(index + 1);
            });
        }


        // ✅ Toast Notification for Successful Add
        @if ($success != '')
            Swal.fire({
                toast: true,
                position: 'top-end',
                icon: 'success',
                title: '{{ $success }}',
                showConfirmButton: false,
                timer: 3000,
                timerProgressBar: true,
                // background: '#d4edda',
                // color: '#155724',
                didOpen: (toast) => {
                    toast.addEventListener('mouseenter', Swal.stopTimer);
                    toast.addEventListener('mouseleave', Swal.resumeTimer);
                }
            });
        @endif

        // ⚠️ Warning Toast
        @if ($warning != '')
            Swal.fire({
                toast: true,
                position: 'top-end',
                icon: 'warning',
                title: '{{ $warning }}',
                showConfirmButton: false,
                timer: 3000,
                timerProgressBar: true
            });
        @endif

        // 🗑️ Toast for Successful Delete
        @if (session('delete_success'))
            Swal.fire({
                toast: true,
                position: 'top-end',
                icon: 'success',
                title: '{{ session('delete_success') }}',
                showConfirmButton: false,
                timer: 3000,
                timerProgressBar: true,
                background: '#f8d7da',
                color: '#721c24',
            });
        @endif
    </script>
@endsection
