@extends('layouts.layout')
@section('pageTitle')
    <strong>Unit set-up</strong>
@endsection

@section('content')
    <style>
        .table>thead>tr>th {
            vertical-align: middle;
            text-align: center;
            font-weight: 600;
            background-color: #f0f0f0;
            border-bottom: 2px solid #ddd;
        }

        .table>tbody>tr:hover {
            background-color: #f9f9f9;
        }

        .btn-xs {
            padding: 3px 8px;
            font-size: 12px;
        }

        .text-right {
            margin-top: 5px;
        }

        .panel {
            border-radius: 6px;
            box-shadow: 0 1px 4px rgba(0, 0, 0, 0.15);
        }

        .panel-heading {
            background-color: #2c3e50;
            color: #fff;
        }

        .form-group label {
            font-weight: 600;
        }

        .swal2-title-custom {
            font-size: 18px !important;
            font-weight: 600;
        }


    </style>
    </style>
    @php
        $CourtInfo =
            $CourtInfo ??
            (object) [
                'courtstatus' => 1,
                'courtid' => null,
                'divisionstatus' => 1,
                'divisionid' => null,
            ];
    @endphp


    <div class="panel panel-default" style="margin-top:20px; border-radius:6px; box-shadow:0 2px 6px rgba(0,0,0,0.1);">

        <!-- Panel Header -->
        <div class="panel-heading" style="border-radius:6px 6px 0 0;">
            <h3 class="panel-title">
                <i class="fa fa-folder-open"></i> @yield('pageTitle')
                <span id="processing"></span>
            </h3>
        </div>

        <div class="panel-body">

            {{-- Warning Message --}}
            @if ($warning != '')
                <div class="alert alert-danger alert-dismissible">
                    <button type="button" class="close" data-dismiss="alert">&times;</button>
                    <strong>{{ $warning }}</strong>
                </div>
            @endif

            {{-- Success Message --}}
            @if ($success != '')
                <div class="alert alert-success alert-dismissible">
                    <button type="button" class="close" data-dismiss="alert">&times;</button>
                    <strong>{{ $success }}</strong>
                </div>
            @endif

            {{-- Validation Errors --}}
            @if (count($errors) > 0)
                <div class="alert alert-danger alert-dismissible">
                    <button type="button" class="close" data-dismiss="alert">&times;</button>
                    <strong>Error!</strong>
                    @foreach ($errors->all() as $error)
                        <p>{{ $error }}</p>
                    @endforeach
                </div>
            @endif



            <form method="post" id="thisform1" name="thisform1">
                {{ csrf_field() }}

                <!-- Inner Card -->
                <div class="panel panel-primary" style="border-radius:6px;">
                    <div class="panel-heading">
                        <h3 class="panel-title">
                            <i class="fa fa-plus-circle"></i> <strong>Add New Unit</strong>
                        </h3>
                    </div>

                    <div class="panel-body">

                        <div class="row">

                            <div class="col-md-4">
                                <div class="form-group">
                                    <label>Department</label>
                                    <select name="department" class="form-control department" onchange="ReloadForm()"
                                        required>
                                        <option value="">- Select Department -</option>
                                        @foreach ($DepartmentList as $a)
                                            <option value="{{ $a->id }}"
                                                {{ $department == $a->id ? 'selected' : '' }}>
                                                {{ $a->department }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>

                            <div class="col-md-4">
                                <div class="form-group">
                                    <label>Post</label>
                                    <input type="text" name="unit" class="form-control" placeholder="Input Post"
                                        required>
                                </div>
                            </div>

                            <div class="col-md-3">
                                <div class="form-group" style="margin-top:25px;">
                                    <button type="submit" class="btn btn-success btn-block" name="add">
                                        <i class="fa fa-floppy-o"></i> Add New
                                    </button>


                                </div>
                            </div>
                        </div>

                    </div>
                </div>

                <input id="delcode" type="hidden" name="delcode">

                <!-- Table Card -->
                <div class="panel panel-primary" style="margin-top:10px; border-radius:6px;">
                    <div class="panel-heading">
                        <h3 class="panel-title">
                            <i class="fa fa-list"></i> Unit List
                        </h3>
                    </div>

                    <div class="panel-body">

                        <div class="table-responsive" style="font-size:13px;">
                            <table class="table table-bordered table-striped table-hover">
                                <thead>
                                    <tr style="background:#f5f5f5; color:#333;">
                                        <th width="5%">S/N</th>
                                        <th>Department</th>
                                        <th>Unit</th>
                                        <th width="15%" class="text-center">Action</th>
                                    </tr>
                                </thead>

                                <tbody>
                                    @php
                                        $serialNum = ($UnitList->currentPage() - 1) * $UnitList->perPage() + 1;
                                    @endphp

                                    @foreach ($UnitList as $b)
                                        <tr>
                                            <td>{{ $serialNum++ }}</td>
                                            <td>{{ $b->department }}</td>
                                            <td>{{ $b->unit }}</td>
                                            <td class="text-center">
                                                <button type="button" class="btn btn-xs btn-primary"
                                                    onclick="editfunc('{{ $b->unit }}', '{{ $b->unitID }}', '{{ $b->departmentID }}')">
                                                    <i class="fa fa-edit"></i> Edit
                                                </button>

                                                <button type="button" class="btn btn-xs btn-danger"
                                                    onclick="delfunc('{{ $b->unitID }}', '{{ $b->departmentID }}')">
                                                    <i class="fa fa-trash"></i> Delete
                                                </button>
                                            </td>
                                        </tr>
                                    @endforeach

                                </tbody>
                            </table>

                            <!-- Pagination -->
                            <div class="text-right">
                                {{ $UnitList->links() }}
                            </div>

                        </div>

                    </div>
                </div>

            </form>

        </div>
    </div>


    <div id="editModal" class="modal fade" tabindex="-1" role="dialog" aria-labelledby="editModalLabel">
        <div class="modal-dialog" role="document">
            <div class="modal-content" style="border-radius: 10px; box-shadow: 0 4px 10px rgba(0,0,0,0.2);">

                <div class="modal-body" style="padding: 0;">
                    <div class="panel panel-default" style="margin: 0;">
                        <div class="panel-heading "
                            style="background-color: #449d44; color: #fff; border-top-left-radius: 10px; border-top-right-radius: 10px;">
                            <h4 class="panel-title" style="margin: 0; padding: 8px 10px;">
                                <i class="glyphicon glyphicon-edit"></i> Edit Unit
                                <button type="button" class="close" data-dismiss="modal" aria-label="Close"
                                    style="color: #fff; opacity: 1;">
                                    <span aria-hidden="true">&times;</span>
                                </button>
                            </h4>
                        </div>

                        <div class="panel-body" style="padding: 20px;">
                            <form class="form-horizontal" id="editLgaModal" name="editLgaModal" method="POST"
                                action="{{ url('basic/unit/edit') }}">
                                {{ csrf_field() }}

                                <div class="form-group">
                                    <label for="DepID" class="col-sm-3 control-label">Department</label>
                                    <div class="col-sm-8">
                                        <select name="DeptID" id="DeptID" class="form-control department" required>
                                            <option value="">-- Select Department --</option>
                                            @foreach ($DepartmentList as $a)
                                                <option value="{{ $a->id }}">{{ $a->department }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>

                                <div class="form-group">
                                    <label for="designation" class="col-sm-3 control-label">Unit</label>
                                    <div class="col-sm-8">
                                        {{-- <input type="text" class="form-control" id="designation" name="designation"
                                            placeholder="Enter designation"> --}}
                                        <input type="text" class="form-control" id="unit" name="unit"
                                            placeholder="Enter unit">
                                    </div>
                                </div>

                                {{-- <input type="hidden" id="court" name="CourtID"> --}}
                                <input type="hidden" id="PostID" name="PostID">

                                <div class="text-right" style="margin-top: 25px;">
                                    <button type="submit" class="btn btn-success">
                                        <i class="glyphicon glyphicon-ok"></i> Save changes
                                    </button>
                                    <button type="button" class="btn btn-default" data-dismiss="modal">
                                        <i class="glyphicon glyphicon-remove"></i> Close
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </div>




    </div>


    </div>



@endsection

@section('styles')
    <link rel="stylesheet" type="text/css" href="{{ asset('assets/css/datepicker.min.css') }}">
    <style type="text/css">
        .modal-dialog {
            width: 10cm
        }

        .modal-header {

            background-color: #006600;

            color: #FFF;

        }
    </style>
@endsection


@section('scripts')
    <script src="{{ asset('assets/js/jquery-ui.min.js') }}"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <script type="text/javascript">
        function ReloadForm() {
            document.getElementById('thisform1').submit();
        }

        function ReloadFormcourtdivision() {
            document.getElementById('department').value = '';
            document.getElementById('thisform1').submit();
        }

        // function editfunc(a, b, c, d) {
        //     $(document).ready(function() {
        //         $('#designation').val(a);
        //         $('#id').val(b);
        //         $('#court').val(c);
        //         $('#DepID').val(d);
        //         $("#editModal").modal('show');
        //     });
        // }

        // function editfunc(unit, unitID, deptID) {
        //     $('#designation').val(unit);
        //     $('#id').val(unitID);
        //     $('#DepID').val(deptID);
        //     $("#editModal").modal('show');
        // }

        function editfunc(unit, unitID, deptID) {

            $('#unit').val(unit);
            $('#PostID').val(unitID);
            $('#DeptID').val(deptID);

            $("#editModal").modal('show');
        }



        // function delfunc(postID, deptID, courtID) {
        //     Swal.fire({
        //         title: 'Are you sure?',
        //         text: "This will permanently delete the designation.",
        //         icon: 'warning',
        //         showCancelButton: true,
        //         confirmButtonColor: '#d33',
        //         cancelButtonColor: '#3085d6',
        //         confirmButtonText: 'Yes, delete it!',
        //         cancelButtonText: 'Cancel',
        //         reverseButtons: true
        //     }).then((result) => {
        //         if (result.isConfirmed) {
        //             // ✅ Submit delete form automatically
        //             $('<form>', {
        //                     'method': 'POST',
        //                     'action': "{{ url('basic/designation') }}",
        //                 })
        //                 .append('@csrf')
        //                 .append($('<input>', {
        //                     'type': 'hidden',
        //                     'name': 'PostID',
        //                     'value': postID
        //                 }))
        //                 .append($('<input>', {
        //                     'type': 'hidden',
        //                     'name': 'depty',
        //                     'value': deptID
        //                 }))
        //                 .append($('<input>', {
        //                     'type': 'hidden',
        //                     'name': 'court',
        //                     'value': courtID
        //                 }))
        //                 .appendTo('body')
        //                 .submit();
        //         }
        //     });
        // }

        function delfunc(postID, deptID) {
            Swal.fire({
                title: 'Are you sure?',
                text: "This will permanently delete the unit.",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#3085d6',
                confirmButtonText: 'Yes, delete it!'
            }).then((result) => {
                if (result.isConfirmed) {

                    $('<form>', {
                            method: 'POST',
                            action: "{{ url('basic/unit/delete') }}"
                        })
                        .append('@csrf')
                        .append($('<input>', {
                            type: 'hidden',
                            name: 'PostID',
                            value: postID
                        }))
                        .appendTo('body')
                        .submit();
                }
            });
        }
        // ✅ SweetAlert Toast for success message
        @if (session('success'))
            Swal.fire({
                toast: true,
                position: 'top-end',
                icon: 'success',
                title: '{{ session('success') }}',
                showConfirmButton: false,
                timer: 2500,
                timerProgressBar: true,
                customClass: {
                    title: 'swal-title-lg', // Custom class for larger font
                }
            });
        @endif
    </script>
@endsection
