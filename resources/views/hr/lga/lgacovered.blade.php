@extends('layouts.layout')
@section('pageTitle')
    Local Govement Covered
@endsection



@section('content')
    {{-- <style>
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

        .dataTables_filter {
            float: right !important;
            margin-bottom: 10px !important;


        }

        .dataTables_length label:first-child {
            display: none !important;
        }
    </style> --}}

    <style>
        /* Card Style */
        .card {
            background: #fff;
            border-radius: 8px;
            border: 1px solid #ddd;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.15);
            margin-bottom: 20px;
        }

        .dataTables_length label:first-child {
            display: none !important;
        }

        .dataTables_filter {
            float: right !important;
            margin-bottom: 10px !important;
            display: none !important;


        }

        .card-header {
            background: #337ab7;
            color: #fff;
            padding: 12px 15px;
            border-top-left-radius: 8px;
            border-top-right-radius: 8px;
        }

        .card-title {
            margin: 0;
            font-size: 16px;
            font-weight: 600;
        }

        .card-body {
            padding: 20px;
        }

        .card-footer {
            padding: 12px 15px;
            background: #f7f7f7;
            border-bottom-left-radius: 8px;
            border-bottom-right-radius: 8px;
            text-align: right;
        }

        /* Existing table enhancements */
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

        .panel {
            border-radius: 6px;
            box-shadow: 0 1px 4px rgba(0, 0, 0, 0.15);
        }

        /* .panel-heading {
                    background-color: #337ab7 !important;
                    color: #fff !important;
                } */
    </style>


    <div id="editModal" class="modal fade" tabindex="-1" role="dialog" aria-labelledby="editModalLabel">
        <div class="modal-dialog" role="document">
            <div class="modal-content">

                <!-- Card header -->
                <div class="modal-header " style="color: #fff;">
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close" style="color: #fff;">
                        <span aria-hidden="true">&times;</span>
                    </button>
                    <h4 class="modal-title" id="editModalLabel">
                        <i class="fa fa-edit"></i> Edit Local Government Area
                    </h4>
                </div>

                <!-- Card body -->
                <form class="form-horizontal" id="editLgaModal" name="editLgaModal" method="POST"
                    action="{{ url('lga/covered/edit') }}">
                    {{ csrf_field() }}
                    <div class="modal-body">
                        <div class="panel panel-default" style="box-shadow: 0 2px 6px rgba(0,0,0,0.2); border-radius: 4px;">
                            <div class="panel-body">

                                <div class="form-group">
                                    <label for="lgaChange" class="col-sm-3 control-label">LGA Name</label>
                                    <div class="col-sm-8">
                                        <input type="text" class="form-control" id="lgaChange" name="lgaChange"
                                            placeholder="Enter Local Government Area">
                                        <input type="hidden" id="lgaid" name="lgaid">
                                    </div>
                                </div>

                            </div>
                        </div>
                    </div>

                    <!-- Card footer -->
                    <div class="modal-footer">
                        <button type="submit" class="btn btn-success">
                            <i class="fa fa-save"></i> Save changes
                        </button>
                        <button type="button" class="btn btn-default" data-dismiss="modal">
                            <i class="fa fa-times"></i> Close
                        </button>
                    </div>
                </form>

            </div>
        </div>
    </div>


    <div class="box box-default panel panel-default">
        <div class="box-body box-profile">
            <div class="box-header with-border hidden-print panel-heading">
                <h3 class="box-title panel-title"> <i class="glyphicon glyphicon-map-marker"></i> @yield('pageTitle') <span
                        id='processing'></span></h3>
            </div>
            <div class="box-body">
                <div class="row">
                    <div class="col-md-12"><!--1st col-->
                        {{-- @include('hr.Share.message') --}}

                        <form class="form-horizontal" role="form" method="post" action="{{ url('lga/covered/add') }}">
                            {{ csrf_field() }}

                            <div class="panel panel-primary">
                                {{-- <div class="panel-heading">
                                    <h3 class="panel-title"><strong>Add New Local Government</strong></h3>
                                </div> --}}
                                <div class="panel-heading">
                                    <h3 class="panel-title">
                                        <i class="fa fa-plus-circle"></i>
                                        <strong>Add New Local Government</strong>
                                    </h3>
                                </div>
                                <div class="panel-body">
                                    <div class="row" style="padding: 10px">


                                        <div class="col-md-4" style="margin-right: 15px">
                                            <div class="form-group">
                                                <label>State</label>


                                                <select class="form-control department" id="state" name="state">
                                                    <option value="">-select State-</option>
                                                    @foreach ($getStates as $list)
                                                        <option value="{{ $list->StateID }}"
                                                            {{ $StateID == $list->StateID ? 'selected' : '' }}>
                                                            {{ $list->State }}
                                                        </option>
                                                    @endforeach
                                                </select>
                                            </div>
                                        </div>

                                        <div class="col-md-4" style="margin-right: 20px">
                                            <div class="form-group">
                                                <label>Local Govement Area</label>
                                                <input type="text" class="form-control" id="localGovernmentArea"
                                                    name="localGovernmentArea" placeholder="">
                                            </div>
                                        </div>



                                        <div class="col-md-2">
                                            <div class="form-group" style="margin-top:25px;">
                                                <button type="submit" class="btn btn-success btn-block" name="add">
                                                    <i class="fa fa-floppy-o"></i> Add New
                                                </button>

                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <!-- /.col -->
                    </div>








                    <!-- /.row -->

                    </form>





                    <hr />
                </div>

                <div class="card" style="margin-top: 20px;">
                    {{-- <div class="card-header">
                        <h3 class="card-title">Local Government List</h3>
                    </div> --}}

                    <div class="card-header">
                        <h3 class="card-title">
                            <i class="glyphicon glyphicon-folder-open"></i>
                            Local Government List
                        </h3>
                    </div>

                    <div class="card-body table-responsive" style="font-size:13px;">
                        <table id="mytable" class="table table-bordered table-striped table-hover">
                            <thead>
                                <tr>
                                    <th style="width: 10%">S/N</th>
                                    <th style="width: 50%">NAME</th>
                                    <th style="width: 10%" class="text-center">Action</th>
                                </tr>
                            </thead>

                            @php $i=1; @endphp
                            @foreach ($getLGA as $list)
                                <tr>
                                    <td>{{ $i++ }}</td>
                                    <td>{{ $list->lga }}</td>

                                    <td class="text-center">
                                        <button type="button" class="btn btn-xs btn-primary"
                                            onclick="editfunc('{{ $list->lga }}', '{{ $list->lgaId }}')">
                                            <i class="fa fa-edit"></i> Edit
                                        </button>

                                        <button type="button" class="btn btn-xs btn-danger"
                                            onclick="deleteLGA({{ $list->lgaId }})">
                                            <i class="fa fa-trash"></i> Delete
                                        </button>
                                    </td>
                                </tr>
                            @endforeach
                        </table>
                    </div>
                </div>

            </div>
        </div>

        <form id="getAllLga" method="post" action="{{ url('lga/covered') }}">
            {{ csrf_field() }}
            <input type="hidden" id="getState" name="getState" />
        </form>
    @endsection

    @section('styles')
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

    {{-- @section('scripts')
        <script src="{{ asset('assets/js/jquery-ui.min.js') }}"></script>
        <script src="https://cdn.datatables.net/1.10.16/js/jquery.dataTables.min.js"></script>
        <script src="https://cdn.datatables.net/1.10.16/js/dataTables.bootstrap.min.js"></script>
        <script>
            function editfunc(x, y) {
                $(document).ready(function() {
                    $('#lgaChange').val(x);
                    $('#lgaid').val(y);
                    $("#editModal").modal('show');
                });
            }



            $('#state').change(function() {
                $('#getState').val($('#state').val());
                $('#getAllLga').submit();
            });

            $(document).ready(function() {
                $('#mytable').DataTable();
            });
            // delete function for LGA
            function deleteLGA(lgaId) {
                Swal.fire({
                    title: 'Are you sure?',
                    text: "This will permanently delete the LGA. This action cannot be undone.",
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#d33',
                    cancelButtonColor: '#3085d6',
                    confirmButtonText: 'Yes, delete it!',
                    cancelButtonText: 'Cancel',
                    reverseButtons: true
                }).then((result) => {
                    if (result.isConfirmed) {
                        // Create a form and submit it
                        const form = document.createElement('form');
                        form.method = 'POST';
                        form.action = `/lga/covered/remove/${lgaId}`; // Your Laravel route

                        // Add CSRF token
                        const csrfToken = document.createElement('input');
                        csrfToken.type = 'hidden';
                        csrfToken.name = '_token';
                        csrfToken.value = '{{ csrf_token() }}'; // Laravel CSRF token
                        form.appendChild(csrfToken);

                        // Append form to body and submit
                        document.body.appendChild(form);
                        form.submit();
                    }
                });
            }

            @if (session('success'))
                <
                script >
                    $(document).ready(function() {
                        Swal.fire({
                            toast: true,
                            position: 'top-end',
                            icon: 'success',
                            title: '{{ session('success') }}',
                            showConfirmButton: false,
                            timer: 3000,
                            timerProgressBar: true,
                            background: '#d4edda',
                            color: '#155724',
                        });
                    });
        </script>
        @endif

        @if (session('error'))
            <script>
                $(document).ready(function() {
                    Swal.fire({
                        toast: true,
                        position: 'top-end',
                        icon: 'error',
                        title: '{{ session('error') }}',
                        showConfirmButton: false,
                        timer: 3000,
                        timerProgressBar: true,
                        background: '#f8d7da',
                        color: '#721c24',
                    });
                });
            </script>
        @endif



        </script>



    @stop --}}

    @section('scripts')
        <script src="{{ asset('assets/js/jquery-ui.min.js') }}"></script>
        <script src="https://cdn.datatables.net/1.10.16/js/jquery.dataTables.min.js"></script>
        <script src="https://cdn.datatables.net/1.10.16/js/dataTables.bootstrap.min.js"></script>

        <script>
            function editfunc(x, y) {
                $('#lgaChange').val(x);
                $('#lgaid').val(y);
                $("#editModal").modal('show');
            }

            $('#state').change(function() {
                $('#getState').val($('#state').val());
                $('#getAllLga').submit();
            });

            // $(document).ready(function() {
            //     $('#mytable').DataTable();
            // });

            $(document).ready(function() {
                $('#mytable').DataTable({
                    pageLength: 15, // 👈 show 15 rows per page
                    lengthMenu: [10, 15, 20, 30, 50, 100], // optional dropdown options
                });
            });

            // delete function for LGA
            function deleteLGA(lgaId) {
                Swal.fire({
                    title: 'Are you sure?',
                    text: "This will permanently delete the LGA. This action cannot be undone.",
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#d33',
                    cancelButtonColor: '#3085d6',
                    confirmButtonText: 'Yes, delete it!',
                    cancelButtonText: 'Cancel',
                    reverseButtons: true
                }).then((result) => {
                    if (result.isConfirmed) {
                        // Create a form and submit it
                        const form = document.createElement('form');
                        form.method = 'POST';
                        form.action = `/lga/covered/remove/${lgaId}`; // Your Laravel route

                        // Add CSRF token
                        const csrfToken = document.createElement('input');
                        csrfToken.type = 'hidden';
                        csrfToken.name = '_token';
                        csrfToken.value = '{{ csrf_token() }}'; // Laravel CSRF token
                        form.appendChild(csrfToken);

                        // Append form to body and submit
                        document.body.appendChild(form);
                        form.submit();
                    }
                });
            }
        </script>

        {{-- ✅ Toast alerts after redirect --}}
        @if (session('success'))
            <script>
                $(document).ready(function() {
                    Swal.fire({
                        toast: true,
                        position: 'top-end',
                        icon: 'success',
                        title: '{{ session('success') }}',
                        showConfirmButton: false,
                        timer: 3000,
                        timerProgressBar: true,
                        background: '#d4edda',
                        color: '#155724',
                    });
                });
            </script>
        @endif

        @if (session('error'))
            <script>
                $(document).ready(function() {
                    Swal.fire({
                        toast: true,
                        position: 'top-end',
                        icon: 'error',
                        title: '{{ session('error') }}',
                        showConfirmButton: false,
                        timer: 3000,
                        timerProgressBar: true,
                        background: '#f8d7da',
                        color: '#721c24',
                    });
                });
            </script>
        @endif
    @endsection
