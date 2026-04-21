@extends('layouts.layout')
@section('pageTitle')
    <strong>Add New Employee</strong>
@endsection

<style>
        .panel {
            border-radius: 6px;
            box-shadow: 0 2px 6px rgba(0, 0, 0, 0.1);
        }

        .panel-heading {
            border-radius: 6px 6px 0 0 !important;
        }

        thead tr th{
            font-size: 11px !important;
        }
        td{
            font-size: 10px !important;
        }
</style>

@section('content')

    <!-- Page Header -->
    @include('hr.partials.page-header')
    <!-- End Page Header -->



    <div class="panel panel-default">

        <!-- HEADER -->
        <div class="panel-heading hidden-print">
            <div class="row">
                <div class="col-xs-6">
                    <h3 class="panel-title">
                        @yield('pageTitle')
                        <span id="processing"></span>
                    </h3>
                </div>

                <div class="col-xs-6 text-right">
                    <button type="button" id="btnAddNew" class="btn btn-primary btn-sm">
                        <span class="glyphicon glyphicon-plus" style="margin-right:5px;"></span>
                        Add New
                    </button>
                </div>
            </div>
        </div>

        <!-- ERRORS -->
        @if (count($errors) > 0)
            <div class="alert alert-danger alert-dismissible" style="margin:15px;">
                <button type="button" class="close" data-dismiss="alert">&times;</button>
                <strong>Error!</strong>
                @foreach ($errors->all() as $error)
                    <p>{{ $error }}</p>
                @endforeach
            </div>
        @endif

        <!-- FORM -->
        <div id="addForm" style="display:none;" class="panel-body">

            <form method="post" action="{{ route('adminSaveNewStaff') }}" class="form-horizontal">
                {{ csrf_field() }}

                <div class="row">

                    <div class="col-lg-3">
                        <label>Title</label>
                        <select class="form-control" name="title" required>
                            <option value="">-Select-</option>
                            <option>Mr</option>
                            <option>Ms</option>
                            <option>Mrs</option>
                            <option>Miss</option>
                        </select>
                    </div>

                    <div class="col-lg-3">
                        <label>Surname</label>
                        <input class="form-control" name="surname" type="text" required>
                    </div>

                    <div class="col-lg-3">
                        <label>First Name</label>
                        <input class="form-control" name="firstname" type="text" required>
                    </div>

                    <div class="col-lg-3">
                        <label>Other Names</label>
                        <input class="form-control" name="othernames" type="text">
                    </div>

                </div>

                <br>

                <div class="row">

                    <div class="col-lg-3">
                        <label>Username</label>
                        <input class="form-control" name="username" type="text">
                    </div>

                    <div class="col-lg-3">
                        <label>Email</label>
                        <input class="form-control" name="email" type="email">
                    </div>

                    <div class="col-lg-3">
                        <label>Phone No.</label>
                        <input class="form-control" name="phoneNo" type="text">
                    </div>

                    <div class="col-lg-3">
                        <label>Gender</label>
                        <select name="sex" class="form-control" required>
                            <option value="">-Select-</option>
                            <option>Male</option>
                            <option>Female</option>
                        </select>
                    </div>

                </div>

                <br>

                <div class="row">

                    <div class="col-lg-3">
                        <label>Marital Status</label>
                        <select name="maritalStatus" class="form-control" required>
                            <option value="">-Select-</option>
                            <option>Single</option>
                            <option>Married</option>
                            <option>Divorced</option>
                            <option>Widowed</option>
                        </select>
                    </div>

                    <div class="col-lg-3">
                        <label>Department</label>
                        <select name="department_id" id="department_id" class="form-control" required>
                            <option value="">-Select-</option>
                            @foreach ($DepartmentList as $dept)
                                <option value="{{ $dept->id }}">{{ $dept->department }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-lg-3">
                        <label>Unit</label>
                        <select name="unit_id" id="unit_id" class="form-control" required>
                            <option value="">-Select-</option>
                            @foreach ($UnitList as $unit)
                                <option value="{{ $unit->unitID }}">{{ $unit->unit }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-lg-3">
                        <label>IOU Cap</label>
                        <input type="number" name="iou" class="form-control">
                    </div>

                </div>

                <br>

                <div class="row">

                    <div class="col-lg-3">
                        <label>Date of Birth</label>
                        <input type="date" name="date_of_birth" class="form-control" max="{{ date('Y-m-d') }}">
                    </div>

                    <div class="col-lg-3">
                        <label>Date of Joining</label>
                        <input type="date" name="date_of_joining" class="form-control">
                    </div>

                    <div class="col-lg-3">
                        <label>Address</label>
                        <textarea name="address" class="form-control" rows="1"></textarea>
                    </div>

                    <div class="col-lg-3">
                        <label>Designation</label>
                        <select name="designation_id" id="designation_id" class="form-control" required>
                            <option value="">-Select-</option>
                        </select>
                    </div>

                </div>

                <br>

                <button type="submit" class="btn btn-success">
                    <i class="fa fa-floppy-o"></i> Save
                </button>

            </form>
        </div>

    </div>



    <div class="panel panel-default" style="margin-top: 20px;">
        <div class="panel-heading">
            <h3 class="panel-title">Staff List</h3>
        </div>

        <div class="panel-body">
            <div class="table-responsive" >
                <table class="table table-bordered table-striped table-highlight" id="tablr">
                    <thead>
                        <tr bgcolor="#c7c7c7">
                            <th width="1%">S/N</th>
                            <th>FULL NAME</th>
                            <th>DATE OF BIRTH</th>
                            <th>GENDER</th>
                            <th>MARITAL STATUS</th>
                            <th>L.G.A</th>
                            <th>STATE OF ORIGIN</th>
                            <th>DATE OF APPOINTMENT</th>
                            <th>DESIGNATION</th>
                            {{-- <th>DATE OF PRESENT APPOINTMENT</th> --}}
                            <th colspan="2">ACTIONS</th>
                        </tr>
                    </thead>

                    @php $serialNum = 1; @endphp
                    @foreach ($QueryStaffReport as $b)
                        <tr style="{{ $b->staff_status == 0 ? 'background-color: red; color: white;' : '' }}">
                            <td>{{ $serialNum++ }}</td>
                            <td>{{ $b->title . ' ' . $b->surname . ' ' . $b->othernames . ' ' . $b->first_name }}</td>
                            <td>{{ $b->dob ? date('d-M-Y', strtotime($b->dob)) : 'N/A' }}</td>
                            <td>{{ $b->gender }}</td>
                            <td>{{ $b->maritalstatus }}</td>
                            <td>{{ $b->lga }}</td>
                            <td>{{ $b->State }}</td>
                            <td>{{ $b->doj ? date('d-M-Y', strtotime($b->doj)) : 'N/A' }}</td>
                            <td>{{ $b->designation }}</td>
                            {{-- <td>{{ $b->date_present_appointment ? date('d-M-Y', strtotime($b->date_present_appointment)) : 'N/A' }}
                            </td> --}}

                            {{-- <td>
                                <a class="btn btn-success btn-sm" href="javascript: LoadSummary('{{ $b->ID }}')">
                                    Record of Service
                                </a>
                            </td> --}}

                            @if ($b->progress_regID < 18)
                                <td>
                                    <a class="btn btn-primary btn-sm"
                                        href="/continue-staff-documentation/{{ $b->ID }}">
                                        Documentation
                                    </a>
                                </td>
                            @else
                               <td>
                                <a class="btn btn-success btn-sm" href="javascript: LoadSummary('{{ $b->ID }}')">
                                  Staff Record
                                </a>
                               </td>
                            @endif
                        </tr>
                    @endforeach

                </table>
            </div>
        </div>
    </div>

       <form method="post" id="displayform" name="displayform" action="{{ url('/profile/details') }}" target="_blank">

            {{ csrf_field() }}


            <input type="hidden" id="fileno" name="fileNo">


        </form>
@endsection

@section('styles')
    <link rel="stylesheet" type="text/css" href="{{ asset('assets/css/datepicker.min.css') }}">

    <style>
        .card-box {
            background: #fff;
            border: 1px solid #ddd;
            border-radius: 6px;
            box-shadow: 0 2px 6px rgba(0, 0, 0, 0.08);
            margin-bottom: 20px;
        }

        .card-header {
            padding: 12px 15px;
            border-bottom: 1px solid #eee;
            background: #f9f9f9;
        }

        .card-title {
            margin: 0;
            font-size: 16px;
            font-weight: bold;
        }

        .card-body {
            padding: 15px;
        }
    </style>
@endsection

@section('scripts')
<script src="https://cdnjs.cloudflare.com/ajax/libs/moment.js/2.24.0/moment.min.js"></script>
    <script src="{{ asset('assets/js/jquery-ui.min.js') }}"></script>
    <script src="{{ asset('ckeditor/ckeditor.js') }}"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        CKEDITOR.replace('editor');
    </script>

    <script type="text/javascript">
        function LoadSummary(staffid)

        {

            document.getElementById('fileno').value = staffid;
            document.forms["displayform"].submit();

            return;

        }
    </script>

    <script type="text/javascript">
        $("#state").change(function(e) {
            var state_id = e.target.value;
            $.get('../get-lga-from-state?state_id=' + state_id, function(data) {
                $('#lga').empty();
                //console.log(data);
                $('#lga').append('<option value="">Select One</option>');
                $.each(data, function(index, obj) {
                    $('#lga').append('<option value="' + obj.lgaId + '">' + obj.lga + '</option>');
                });


            })
        });
    </script>
    <script>
        // Set today's date as max
        document.getElementById('date_of_birth').max = new Date().toISOString().split("T")[0];
    </script>
    <script>
        // Disable future dates
        document.getElementById('date_of_joining').max = new Date().toISOString().split("T")[0];
    </script>
    <script>
        function autoGrow(field) {
            field.style.height = "auto"; // Reset height
            field.style.height = field.scrollHeight + "px"; // Expand based on content
        }

        function resetSize(field) {
            field.style.height = "auto"; // Reset
            field.rows = 1; // Return to default one line
        }
    </script>
    {{-- <script>
        document.getElementById('department_id').addEventListener('change', function() {
            let deptID = this.value;

            // Clear designation list
            let designationSelect = document.getElementById('designation_id');
            designationSelect.innerHTML = '<option value="">Loading...</option>';

            if (deptID) {
                fetch(`/get-designations/${deptID}`)
                    .then(response => response.json())
                    .then(data => {
                        designationSelect.innerHTML = '<option value="">-Select-</option>';

                        data.forEach(function(item) {
                            designationSelect.innerHTML +=
                                `<option value="${item.id}">${item.designation}</option>`;
                        });
                    })
                    .catch(error => {
                        designationSelect.innerHTML = '<option value="">Error loading...</option>';
                    });
            } else {
                designationSelect.innerHTML = '<option value="">-Select-</option>';
            }
        });
    </script> --}}

    <script>
        $('#department_id').on('change', function() {
            let deptID = $(this).val();

            // Reset fields
            $('#unit_id').html('<option value="">Loading...</option>');
            $('#designation_id').html('<option value="">Loading...</option>');

            if (deptID) {

                // LOAD UNITS
                $.ajax({
                    url: '/ajax/get-units/' + deptID,
                    type: 'GET',
                    success: function(data) {
                        $('#unit_id').empty().append('<option value="">-Select-</option>');
                        $.each(data, function(key, unit) {
                            $('#unit_id').append('<option value="' + unit.unitID + '">' + unit
                                .unit + '</option>');
                        });
                    }
                });

                // LOAD DESIGNATIONS
                $.ajax({
                    url: '/get-designations/' + deptID,
                    type: 'GET',
                    success: function(data) {
                        $('#designation_id').empty().append('<option value="">-Select-</option>');
                        $.each(data, function(key, desig) {
                            $('#designation_id').append('<option value="' + desig.id + '">' +
                                desig.designation + '</option>');
                        });
                    }
                });

            } else {
                $('#unit_id').html('<option value="">-Select-</option>');
                $('#designation_id').html('<option value="">-Select-</option>');
            }
        });
    </script>
    <script>
        $(document).ready(function() {
            $("#btnAddNew").click(function() {
                $("#addForm").slideToggle(); // show/hide animation
            });
        });
    </script>
    @if (session('message'))
        <script>
            Toast.fire({
                icon: 'success',
                title: "{{ session('message') }}"
            });
        </script>
    @endif
    @if (session('error_message'))
        <script>
            Toast.fire({
                icon: 'error',
                title: "{{ session('error_message') }}"
            });
        </script>
    @endif
@endsection
