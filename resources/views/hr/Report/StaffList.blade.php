@extends('layouts.layout')
@section('pageTitle')
    Staff List
@endsection

@section('content')
    <div class="panel panel-success">

        <div class="panel-heading hidden-print" style="position: relative;">
            <h3 class="panel-title">
                @yield('pageTitle') <span id="processing"></span>

                <!-- PRINT BUTTON (right aligned) -->
                <button type="button" class="btn btn-success" onclick="return myFunc();"
                    style="float: right; margin-top: -7px;">
                    <i class="fa fa-print"></i> Print
                </button>
            </h3>
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
        <form method="post" id="thisform1">
            {{-- <form method="post" action="{{ route('staff.list') }}" id="thisform1"> --}}
            {{-- <form method="get" action="{{ url('/report/staff-list') }}" id="thisform1"> --}}
            {{ csrf_field() }}
            <div class="panel-body">



                <div class="row">
                    <div class="col-md-12">
                        <div class="panel panel-success" style="margin-top:20px;">

                            <!-- Header -->
                            <div class="panel-heading">
                                <h3 class="panel-title"><i class="fa fa-filter"></i> Filter Staff Records</h3>
                            </div>

                            <!-- Body -->
                            <div class="panel-body">
                                <div class="row">

                                    <!-- Department -->
                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <label>Department</label>
                                            <select name="department" id="department" class="form-control">
                                                <option value="" selected>-All departments-</option>
                                                @foreach ($DepartmentList as $b)
                                                    <option value="{{ $b->id }}"
                                                        {{ $selectedDepartment == $b->id ? 'selected' : '' }}>
                                                        {{ $b->department }}
                                                    </option>

                                                    {{-- <option value="{{ $b->id }}"
                                                        {{ $selectedDepartment == $b->id ? 'selected' : '' }}>
                                                        {{ $b->department }}
                                                    </option> --}}
                                                @endforeach
                                            </select>
                                        </div>
                                    </div>

                                    <!-- Designation -->
                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <label>Designation</label>
                                            <select name="designation" id="designation" class="form-control">
                                                <option value="" selected>-All designations-</option>
                                                @foreach ($DesignationList as $b)
                                                    <option value="{{ $b->id }}"
                                                        {{ $selectedDesignation == $b->id ? 'selected' : '' }}>
                                                        {{ $b->designation }}
                                                    </option>
                                                    {{-- <option value="{{ $b->id }}"
                                                        {{ $request->designation == $b->id ? 'selected' : '' }}>
                                                        {{ $b->designation }}
                                                    </option> --}}
                                                    {{-- <option value="{{ $b->id }}"
                                                        {{ $selectedDesignation == $b->id ? 'selected' : '' }}>
                                                        {{ $b->designation }}
                                                    </option> --}}
                                                @endforeach
                                            </select>
                                        </div>
                                    </div>

                                    <!-- Buttons -->
                                    <div class="col-md-4">
                                        {{-- <div class="form-group" style="margin-top:25px;">
                                            <button type="submit" class="btn btn-success">
                                                <i class="fa fa-search"></i> Search
                                            </button>


                                        </div> --}}
                                    </div>

                                </div>
                            </div>

                        </div>
                    </div>
                </div>




                <input id="delcode" type="hidden" name="delcode">



                <div class="row">
                    <div class="col-md-12">
                        <div class="panel panel-success" style="margin-top:20px;">

                            <!-- Header -->
                            <div class="panel-heading">
                                <h3 class="panel-title">Staff Records</h3>
                            </div>

                            <!-- Body -->
                            <div class="panel-body">
                                <div class="table-responsive" style="font-size:12px;">

                                    <table class="table table-bordered table-striped table-hover" id="tablr">


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

                                        <tbody id="staffTable">
                                            @foreach ($QueryStaffReport as $b)
                                                <tr
                                                    style="{{ $b->staff_status == 0 ? 'background-color: red; color: white;' : '' }}">
                                                    <td>{{ $serialNum++ }}</td>
                                                    <td>{{ $b->title . ' ' . $b->surname . ' ' . $b->othernames . ' ' . $b->first_name }}
                                                    </td>
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
                                                            <a class="btn btn-success btn-sm"
                                                                href="javascript: LoadSummary('{{ $b->ID }}')">
                                                                Staff Record
                                                            </a>
                                                        </td>
                                                    @endif
                                                </tr>
                                            @endforeach
                                        </tbody>

                                    </table>
                                </div>
                            </div>

                        </div>
                    </div>
                </div>
            </div>

        </form>


        <form method="post" id="displayform" name="displayform" action="{{ url('/profile/details') }}" target="_blank">

            {{ csrf_field() }}


            <input type="hidden" id="fileno" name="fileNo">


        </form>
    </div>
@endsection

@section('styles')
    <link rel="stylesheet" type="text/css" href="{{ asset('assets/css/datepicker.min.css') }}">
    <style type="text/css">
        #fromDate {
            position: relative;
            width: 150px;

            color: white;
        }

        #fromDate:before {
            position: absolute;
            top: -3px;
            left: 3px;
            content: attr(data-date);
            display: inline-block;
            color: grey;
        }

        #fromDate::-webkit-datetime-edit,
        input::-webkit-inner-spin-button,
        input::-webkit-clear-button {
            display: none;
        }

        #fromDate::-webkit-calendar-picker-indicator {
            position: absolute;
            top: 3px;
            right: 0;
            color: grey;
            opacity: 1;
        }


        #toDate {
            position: relative;
            width: 150px;

            color: white;
        }

        #toDate:before {
            position: absolute;
            top: -3px;
            left: 3px;
            content: attr(data-date);
            display: inline-block;
            color: grey;
        }

        #toDate::-webkit-datetime-edit,
        input::-webkit-inner-spin-button,
        input::-webkit-clear-button {
            display: none;
        }

        #toDate::-webkit-calendar-picker-indicator {
            position: absolute;
            top: 3px;
            right: 0;
            color: grey;
            opacity: 1;
        }
    </style>
@endsection

@section('scripts')
    <script src="https://cdnjs.cloudflare.com/ajax/libs/moment.js/2.24.0/moment.min.js"></script>
    <script src="{{ asset('assets/js/jquery-ui.min.js') }}"></script>



    <script type="text/javascript">
        function LoadSummary(staffid)

        {

            document.getElementById('fileno').value = staffid;
            document.forms["displayform"].submit();

            return;

        }
    </script>
    <script type="text/javascript">
        $(document).ready(function() {

            $('#fields').multiselect({
                nonSelectedText: 'Select fields to view',
                enableFiltering: true,
                enableCaseInsensitiveFiltering: true,
                buttonWidth: '400px',
                includeSelectAllOption: true,
            });
        });
    </script>
    <script type="text/javascript">
        $("#fromDate").on("change", function() {
            this.setAttribute(
                "data-date",
                moment(this.value, "YYYY-MM-DD")
                .format(this.getAttribute("data-date-format"))
            )
        }).trigger("change")

        $("#toDate").on("change", function() {
            this.setAttribute(
                "data-date",
                moment(this.value, "YYYY-MM-DD")
                .format(this.getAttribute("data-date-format"))
            )
        }).trigger("change")
    </script>
    <script type="text/javascript">
        function checkForm() {
            var fields = document.getElementById('fields').value;
            var form = document.getElementById('thisform1');
            if (fields == '') {
                alert('Please select fields to view');
                return false;
            } else {
                form.submit();
            }
            return false;
        }

        function ReloadForm() {
            //alert("ururu")	;
            document.getElementById('thisform1').submit();
            return;
        }

        function DeletePromo(id) {
            var cmt = confirm('You are about to delete a record. Click OK to continue?');
            if (cmt == true) {
                document.getElementById('delcode').value = id;
                document.getElementById('thisform1').submit();
                return;

            }

        }
        $(function() {
            $("#todate").datepicker({
                changeMonth: true,
                changeYear: true,
                dateFormat: 'yy-mm-dd'
            });
            $("#fromdate").datepicker({
                changeMonth: true,
                changeYear: true,
                dateFormat: 'yy-mm-dd'
            });
            $("#appointmentDate").datepicker({
                changeMonth: true,
                changeYear: true,
                dateFormat: 'yy-mm-dd'
            });
            $("#incrementalDate").datepicker({
                changeMonth: true,
                changeYear: true,
                dateFormat: 'yy-mm-dd'
            });
            $("#firstArrivalDate").datepicker({
                changeMonth: true,
                changeYear: true,
                dateFormat: 'yy-mm-dd'
            });
        });
    </script>

    <script>
        function myFunc() {
            var printme = document.getElementById('tablr');
            var wme = window.open("", "", "width=900,height=700");
            wme.document.write(printme.outerHTML);
            wme.document.close();
            wme.focus();
            wme.print();
            wme.close();
        }
    </script>
    {{-- <script>
        $(document).ready(function() {

            function loadStaff() {
                var department = $('#department').val();
                var designation = $('#designation').val();

                $.ajax({
                    url: "{{ url('/report/staff-filter') }}",
                    type: "GET",
                    data: {
                        department: department,
                        designation: designation
                    },
                    success: function(data) {
                        $('#staffTable').html(data);
                    }
                });
            }

            $('#department, #designation').on('change', function() {
                loadStaff();
            });

        });
    </script> --}}
    {{-- <script>
        $(document).ready(function() {

            function loadStaff() {
                $.ajax({
                    url: "{{ url('/report/staff-filter') }}",
                    type: "GET",
                    data: {
                        department: $('#department').val(),
                        designation: $('#designation').val()
                    },
                    success: function(data) {
                        $('#staffTable').html(data);
                    }
                });
            }

            $('#department, #designation').on('change', function() {
                loadStaff();
            });

        });
    </script> --}}
    <script>
        $(document).ready(function() {

            function loadStaff() {

                $.ajax({
                    url: "{{ url('/report/staff-filter') }}",
                    type: "GET",
                    data: {
                        department: $('#department').val(),
                        designation: $('#designation').val()
                    },
                    success: function(data) {

                        let rows = '';
                        let sn = 1;

                        if (data.length === 0) {
                            rows = `<tr><td colspan="10" class="text-center">No record found</td></tr>`;
                        } else {

                            $.each(data, function(i, b) {

                                rows += `
                        <tr>
                            <td>${sn++}</td>
                            <td>${b.title ?? ''} ${b.surname ?? ''} ${b.othernames ?? ''} ${b.first_name ?? ''}</td>
                            <td>${b.dob ? formatDate(b.dob) : 'N/A'}</td>
                            <td>${b.gender ?? ''}</td>
                            <td>${b.maritalstatus ?? ''}</td>
                            <td>${b.lga ?? ''}</td>
                            <td>${b.State ?? ''}</td>
                            <td>${b.doj ? formatDate(b.doj) : 'N/A'}</td>
                            <td>${b.designation ?? ''}</td>
                        </tr>`;
                            });
                        }

                        $('#staffTable').html(rows);
                    }
                });
            }

            function formatDate(date) {
                let d = new Date(date);
                return d.toLocaleDateString('en-GB');
            }

            // 🔥 AUTO LOAD ON CHANGE (NO BUTTON NEEDED)
            $('#department, #designation').on('change', function() {
                loadStaff();
            });

        });
    </script>
@endsection
