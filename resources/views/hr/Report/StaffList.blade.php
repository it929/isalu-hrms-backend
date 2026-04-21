@extends('layouts.layout')
@section('pageTitle')
    Staff List
@endsection

@section('content')
    <div class="box box-default">
        <div class="box-header with-border hidden-print">
            <h3 class="box-title">@yield('pageTitle') <span id='processing'></span></h3>
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

                <div class="row mb-3">
                    <div class="col-md-2">
                        <label>Grade</label>
                        <select name="grade" id="grade" class="form-control">
                            <option value="" selected>-Select-</option>
                            @for ($i = 1; $i <= 17; $i++)
                                <option value="{{ $i }}">{{ $i }}</option>
                            @endfor
                        </select>
                    </div>

                    <div class="col-md-3">
                        <label>Department</label>
                        <select name="department" id="department" class="form-control">
                            <option value="" selected>-All departments-</option>
                            @foreach ($DepartmentList as $b)
                                <option value="{{ $b->id }}" {{ $department == $b->id ? 'selected' : '' }}>
                                    {{ $b->department }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-md-3">
                        <label>Designation</label>
                        <select name="designation" id="designation" class="form-control">
                            <option value="" selected>-All designations-</option>
                            @foreach ($DesignationList as $b)
                                <option value="{{ $b->id }}" {{ $b->designation == $b->id ? 'selected' : '' }}>
                                    {{ $b->designation }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Search and Print Buttons -->
                    <div class="col-md-4 d-flex align-items-end" style="margin-top: 24px;">
                        <button type="submit" class="btn btn-success w-100" onclick="return checkForm();" name="add">
                            <i class="fa fa-search"></i> Search
                        </button>
                        <button type="button" class="btn btn-primary" onclick="return myFunc();">
                            <i class="fa fa-print"></i> Print
                        </button>
                    </div>
                </div>




                <input id="delcode" type="hidden" name="delcode">

                <div class="row">
                    <div class="table-responsive" style="font-size: 12px; padding:10px; margin-top:30px;">
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
                                    <th>DATE OF PRESENT APPOINTMENT</th>

                                    <th colspan="2">ACTIONS</th>


                                </tr>
                            </thead>
                            @php $serialNum = 1; @endphp
                            @foreach ($QueryStaffReport as $b)
                                <tr style="{{$b->staff_status == 0 ? 'background-color: red; color: white;' : ''}}">
                                    <td>{{ $serialNum++ }} </td>
                                    <td>{{ $b->title . ' ' . $b->surname . ' ' . $b->othernames . ' ' . $b->first_name }}
                                    </td>

                                    <td class="dob">{{ $b->dob ? date('d-M-Y', strtotime($b->dob)) : 'N/A' }}</td>

                                    <td class="gender">{{ $b->gender }}</td>

                                    <td class="ms">{{ $b->maritalstatus }}</td>

                                    <td class="lga">{{ $b->lga }}</td>

                                    <td class="soo">{{ $b->State }}</td>

                                    <td class="doa">
                                        {{ $b->appointment_date ? date('d-M-Y', strtotime($b->appointment_date)) : 'N/A' }}
                                    </td>

                                    <td class="rank">{{ $b->designation }}</td>

                                    <td class="dopa">
                                        {{ $b->date_present_appointment ? date('d-M-Y', strtotime($b->date_present_appointment)) : 'N/A' }}
                                    </td>

                                    <td class="qua"><span class="btn btn-success text-white"><a
                                                href="javascript: LoadSummary('{{ $b->ID }}')"
                                                style="color:#FFF !important;">Record of Service</a></span>

                                    </td>

                                    @if ($b->progress_regID < 18)
                                    <td class="qua"><span class="btn btn-success text-white"><a
                                                href="/continue-staff-documentation/{{ $b->ID }}"
                                                style="color:#FFF !important;">Documentation </a></span></td>
                                    @endif
                                </tr>
                            @endforeach


                        </table>
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
@endsection
