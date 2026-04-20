@extends('layouts.layout')
@section('pageTitle')
    <strong>Designation set-up</strong>
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
            box-shadow: 0 2px 6px rgba(0, 0, 0, 0.1);
        }

        .panel-heading {
            border-radius: 6px 6px 0 0 !important;
        }

        .form-group label {
            font-weight: 600;
        }

        .swal2-title-custom {
            font-size: 18px !important;
            font-weight: 600;
        }
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
    <div class=" panel panel-default">
        <div class="box-header with-border hidden-print panel-heading">
            <h3 class="box-title panel-title"> <i class="glyphicon glyphicon-dashboard" style="margin-right:6px;"></i> @yield('pageTitle') <span id='processing'></span></h3>
        </div>
        @if ($warning != '')
            <div class="alert alert-dismissible alert-danger">
                <button type="button" class="close" data-dismiss="alert">&times;</button>
                <strong>{{ $warning }}</strong>
            </div>
        @endif
        @if ($success != '')
            <div class="alert alert-dismissible alert-success">
                <button type="button" class="close" data-dismiss="alert">&times;</button>
                <strong>{{ $success }}</strong>
            </div>
        @endif
        @if (count($errors) > 0)
            <div class="alert alert-danger alert-dismissible" role="alert">
                <button type="button" class="close" data-dismiss="alert" aria-label="Close"><span
                        aria-hidden="true">&times;</span>
                </button>
                <strong>Error!</strong>
                @foreach ($errors->all() as $error)
                    <p>{{ $error }}</p>
                @endforeach
            </div>
        @endif
        <form method="post" id="thisform1" name="thisform1">
            {{ csrf_field() }}
            <div class="box-body">

                <div class="panel panel-primary">
                    <div class="panel-heading">
                        <h3 class="panel-title"> <i class="fa fa-plus-circle"></i>  <strong>Add New Designation</strong></h3>
                    </div>
                    <div class="panel-body">
                        <div class="row">
                            @if ($CourtInfo->courtstatus == 1)
                                <div class="col-md-3">
                                    <div class="form-group">
                                        <label class="control-label">Court</label>
                                        <select required class="form-control" id="court" name="court"
                                            onchange="ReloadForm()">
                                            <option value="">- Select Court -</option>
                                            @foreach ($CourtList as $list)
                                                <option value="{{ $list->id }}"
                                                    {{ $court == $list->id ? 'selected' : '' }}>
                                                    {{ $list->court_name }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                            @else
                                <input type="hidden" id="court" name="court" value="{{ $CourtInfo->courtid }}">
                            @endif

                            <div class="col-md-4">
                                <div class="form-group">
                                    <label>Department</label>
                                    <select name="department" class="form-control department" onchange="ReloadForm()"
                                        required>
                                        <option value=''>- Select Department -</option>
                                        @foreach ($DepartmentList as $a)
                                            <option value="{{ $a->id }}"
                                                {{ $department == $a->id ? 'selected' : '' }}>
                                                {{ $a->department }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>

                            {{-- <div class="col-md-3">
                                <div class="form-group">
                                    <label>Grade Level</label>
                                    <select name="level" class="form-control" required>
                                        <option value="">Select Level</option>
                                        @for ($i = 1; $i <= 17; $i++)
                                            <option value="{{ $i }}">{{ $i }}</option>
                                        @endfor
                                    </select>
                                </div>
                            </div> --}}

                            <div class="col-md-4">
                                <div class="form-group">
                                    <label>Designation</label>
                                    <input type="text" name="designation" class="form-control"
                                        placeholder="Input Designation" required>
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


                <input id ="delcode" type="hidden" name="delcode">




                <div class="panel panel-primary">

                    <!-- Card Header -->
                    <div class="panel-heading">
                        <h3 class="panel-title" style="font-weight:600;">
                            <i class="glyphicon glyphicon-folder-open" style="margin-right:12px;"></i>
                            Designation List
                        </h3>
                    </div>

                    <!-- Card Body -->
                    <div class="panel-body">

                        <div class="table-responsive" style="font-size:13px; margin-top:10px;">
                            <table class="table table-bordered table-striped table-hover">
                                <thead>
                                    <tr style="background-color:#f5f5f5; color:#333;">
                                        <th width="5%">S/N</th>

                                        @if ($CourtInfo->courtstatus == 1)
                                            <th>Court</th>
                                        @endif

                                        <th>Department</th>
                                        {{-- <th>Grade Level</th> --}}
                                        <th>Designation</th>
                                        <th width="15%" class="text-center">Action</th>
                                    </tr>
                                </thead>

                                <tbody>
                                    @php
                                        $serialNum =
                                            ($DesignationList->currentPage() - 1) * $DesignationList->perPage() + 1;
                                    @endphp

                                    @foreach ($DesignationList as $b)
                                        <tr>
                                            <td>{{ $serialNum++ }}</td>

                                            @if ($CourtInfo->courtstatus == 1)
                                                <td>{{ $b->court_name }}</td>
                                            @endif

                                            <td>{{ $b->department }}</td>
                                            {{-- <td>{{ $b->grade }}</td>x --}}
                                            <td>{{ $b->designation }}</td>

                                            <td class="text-center">
                                                <button type="button" class="btn btn-xs btn-primary"
                                                    onclick="editfunc('{{ $b->designation }}', '{{ $b->id }}', '{{ $b->courtID }}', '{{ $b->departmentID }}')">
                                                    <i class="fa fa-edit"></i> Edit
                                                </button>

                                                <button type="button" class="btn btn-xs btn-danger"
                                                    onclick="delfunc('{{ $b->id }}', '{{ $b->departmentID }}', '{{ $b->courtID }}')">
                                                    <i class="fa fa-trash"></i> Delete
                                                </button>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>

                            <!-- Pagination -->
                            <div class="text-right">
                                {{ $DesignationList->links() }}
                            </div>
                        </div>

                    </div>
                </div>




            </div>

        </form>

    </div>


    <div id="editModal" class="modal fade" tabindex="-1" role="dialog" aria-labelledby="editModalLabel">
        <div class="modal-dialog" role="document">
            <div class="modal-content" style="border-radius: 10px; box-shadow: 0 4px 10px rgba(0,0,0,0.2);">

                <div class="modal-body" style="padding: 0;">
                    <div class="panel panel-default" style="margin: 0;">
                        <div class="panel-heading "
                            style="background-color: #449d44; color: #fff; border-top-left-radius: 10px; border-top-right-radius: 10px;">
                            <h4 class="panel-title" style="margin: 0; padding: 8px 10px;">
                                <i class="glyphicon glyphicon-edit"></i> Edit Designation
                                <button type="button" class="close" data-dismiss="modal" aria-label="Close"
                                    style="color: #fff; opacity: 1;">
                                    <span aria-hidden="true">&times;</span>
                                </button>
                            </h4>
                        </div>

                        <div class="panel-body" style="padding: 20px;">
                            <form class="form-horizontal" id="editLgaModal" name="editLgaModal" method="POST"
                                action="{{ url('basic/designation/edit') }}">
                                {{ csrf_field() }}

                                <div class="form-group">
                                    <label for="DepID" class="col-sm-3 control-label">Department</label>
                                    <div class="col-sm-8">
                                        <select name="DeptID" id="DepID" class="form-control department" required>
                                            <option value="">-- Select Department --</option>
                                            @foreach ($DepartmentList as $a)
                                                <option value="{{ $a->id }}">{{ $a->department }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>

                                <div class="form-group">
                                    <label for="designation" class="col-sm-3 control-label">Designation</label>
                                    <div class="col-sm-8">
                                        <input type="text" class="form-control" id="designation" name="designation"
                                            placeholder="Enter designation">
                                    </div>
                                </div>

                                <input type="hidden" id="court" name="CourtID">
                                <input type="hidden" id="id" name="PostID">

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

        function editfunc(a, b, c, d) {
            $(document).ready(function() {
                $('#designation').val(a);
                $('#id').val(b);
                $('#court').val(c);
                $('#DepID').val(d);
                $("#editModal").modal('show');
            });
        }



        function delfunc(postID, deptID, courtID) {
            Swal.fire({
                title: 'Are you sure?',
                text: "This will permanently delete the designation.",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#3085d6',
                confirmButtonText: 'Yes, delete it!',
                cancelButtonText: 'Cancel',
                reverseButtons: true
            }).then((result) => {
                if (result.isConfirmed) {
                    // ✅ Submit delete form automatically
                    $('<form>', {
                            'method': 'POST',
                            'action': "{{ url('basic/designation') }}",
                        })
                        .append('@csrf')
                        .append($('<input>', {
                            'type': 'hidden',
                            'name': 'PostID',
                            'value': postID
                        }))
                        .append($('<input>', {
                            'type': 'hidden',
                            'name': 'depty',
                            'value': deptID
                        }))
                        .append($('<input>', {
                            'type': 'hidden',
                            'name': 'court',
                            'value': courtID
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
